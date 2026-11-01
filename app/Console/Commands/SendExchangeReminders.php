<?php

namespace App\Console\Commands;

use App\Mail\ExchangeRevealReminderMail;
use App\Mail\ExchangeWishlistNudgeMail;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendExchangeReminders extends Command
{
    protected $signature = 'exchanges:send-reminders';

    protected $description = 'Send reminder emails to exchange participants who haven\'t viewed their draw or created a wishlist';

    public function handle(): int
    {
        $this->sendRevealReminders();
        $this->sendWishlistNudges();

        return self::SUCCESS;
    }

    private function sendRevealReminders(): void
    {
        $participants = GiftExchangeParticipant::query()
            ->whereNotNull('email')
            ->whereNull('reminders_muted_at')
            ->whereNull('viewed_at')
            ->whereNull('reveal_reminder_sent_at')
            ->whereHas('exchange', fn ($q) => $q
                ->where('status', 'drawn')
                ->where('draw_completed_at', '<=', now()->subDays(3))
                ->where('event_date', '>', now())
            )
            ->with('exchange')
            ->get();

        $sent = 0;
        foreach ($participants as $participant) {
            if ($participant->isTokenExpired()) {
                continue;
            }

            /** @var GiftExchange $exchange */
            $exchange = $participant->exchange;
            $participant->getConnection()->transaction(function () use ($participant, $exchange, &$sent): void {
                $locked = GiftExchangeParticipant::query()->lockForUpdate()->findOrFail($participant->id);
                if ($locked->reveal_reminder_sent_at || $locked->reminders_muted_at) {
                    return;
                }
                Mail::to($locked->email)->locale($exchange->locale)
                    ->queue((new ExchangeRevealReminderMail($locked, $exchange))->afterCommit());
                $locked->update(['reveal_reminder_sent_at' => now()]);
                $sent++;
            });
        }

        $this->info("Sent {$sent} reveal reminders.");
    }

    private function sendWishlistNudges(): void
    {
        $participants = GiftExchangeParticipant::query()
            ->whereNotNull('email')
            ->whereNull('reminders_muted_at')
            ->whereNull('wishlist_nudge_sent_at')
            ->whereDoesntHave('wishlist.gifts')
            ->whereDoesntHave('user.createdLists', fn ($query) => $query->where('is_default', true)->whereHas('gifts'))
            ->whereNotNull('viewed_at')
            ->where('viewed_at', '<=', now()->subDays(1))
            ->whereHas('exchange', fn ($q) => $q
                ->where('status', 'drawn')
                ->where('event_date', '>', now())
            )
            ->with('exchange')
            ->get();

        $sent = 0;
        foreach ($participants as $participant) {
            if ($participant->isTokenExpired()) {
                continue;
            }

            /** @var GiftExchange $exchange */
            $exchange = $participant->exchange;
            $participant->getConnection()->transaction(function () use ($participant, $exchange, &$sent): void {
                $locked = GiftExchangeParticipant::query()->lockForUpdate()->findOrFail($participant->id);
                if ($locked->wishlist_nudge_sent_at || $locked->reminders_muted_at) {
                    return;
                }
                Mail::to($locked->email)->locale($exchange->locale)
                    ->queue((new ExchangeWishlistNudgeMail($locked, $exchange))->afterCommit());
                $locked->update(['wishlist_nudge_sent_at' => now()]);
                $sent++;
            });
        }

        $this->info("Sent {$sent} wishlist nudges.");
    }
}

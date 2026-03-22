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
            ->whereNull('viewed_at')
            ->whereHas('exchange', fn ($q) => $q
                ->where('status', 'drawn')
                ->where('draw_completed_at', '<=', now()->subDays(3))
                ->where('event_date', '>', now())
            )
            ->with('exchange')
            ->get();

        foreach ($participants as $participant) {
            if ($participant->isTokenExpired()) {
                continue;
            }

            /** @var GiftExchange $exchange */
            $exchange = $participant->exchange;
            Mail::to($participant->email)
                ->queue(new ExchangeRevealReminderMail($participant, $exchange));
        }

        $this->info("Sent {$participants->count()} reveal reminders.");
    }

    private function sendWishlistNudges(): void
    {
        $participants = GiftExchangeParticipant::query()
            ->whereNotNull('viewed_at')
            ->where('viewed_at', '<=', now()->subDays(1))
            ->whereNull('user_id')
            ->whereHas('exchange', fn ($q) => $q
                ->where('status', 'drawn')
                ->where('event_date', '>', now())
            )
            ->with('exchange')
            ->get();

        foreach ($participants as $participant) {
            /** @var GiftExchange $exchange */
            $exchange = $participant->exchange;
            Mail::to($participant->email)
                ->queue(new ExchangeWishlistNudgeMail($participant, $exchange));
        }

        $this->info("Sent {$participants->count()} wishlist nudges.");
    }
}

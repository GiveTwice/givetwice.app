<?php

namespace App\Jobs;

use App\Mail\ExchangeRevealReminderMail;
use App\Mail\ExchangeWishlistNudgeMail;
use App\Models\GiftExchangeParticipant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendExchangeReminder implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $participantId, public bool $wishlistNudge = false) {}

    public function handle(): void
    {
        (new GiftExchangeParticipant)->getConnection()->transaction(function (): void {
            $participant = GiftExchangeParticipant::query()->lockForUpdate()->find($this->participantId);
            if (! $participant) {
                return;
            }
            if ($participant->reminders_muted_at) {
                return;
            }
            if (blank($participant->email)) {
                return;
            }
            if ($participant->isTokenExpired()) {
                return;
            }
            $exchange = $participant->exchange;
            if (! $exchange?->isDrawn()) {
                return;
            }
            if (! $exchange->event_date?->isFuture()) {
                return;
            }
            $column = $this->wishlistNudge ? 'wishlist_nudge_sent_at' : 'reveal_reminder_sent_at';
            if ($participant->{$column}) {
                return;
            }
            if ($this->wishlistNudge) {
                if (! $participant->viewed_at?->lte(now()->subDay())) {
                    return;
                }
                if ($participant->defaultWishlist()?->gifts()->exists()) {
                    return;
                }
            } else {
                if ($participant->viewed_at) {
                    return;
                }
                if (! $exchange->draw_completed_at?->lte(now()->subDays(3))) {
                    return;
                }
            }
            $mail = $this->wishlistNudge
                ? new ExchangeWishlistNudgeMail($participant, $exchange)
                : new ExchangeRevealReminderMail($participant, $exchange);
            Mail::to($participant->email)->send($mail->locale($participant->user->locale_preference ?? $exchange->locale));
            $participant->update([$column => now()]);
        });
    }
}

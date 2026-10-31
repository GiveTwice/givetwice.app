<?php

namespace App\Listeners;

use App\Events\GiftClaimed;
use App\Mail\ReservationMail;
use Illuminate\Support\Facades\Mail;

class SendGaveTwiceEmail
{
    public function handle(GiftClaimed $event): void
    {
        $claim = $event->claim;

        $email = $claim->claimer_email ?? $claim->user?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->queue((new ReservationMail($claim, app()->getLocale()))->locale(app()->getLocale()));
    }
}

<?php

namespace App\Listeners;

use App\Events\GiftClaimed;
use App\Mail\GaveTwiceMail;
use Illuminate\Support\Facades\Mail;

class SendGaveTwiceEmail
{
    public function handle(GiftClaimed $event): void
    {
        $claim = $event->claim;
        $gift = $event->gift;

        $email = $claim->claimer_email ?? $claim->user?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->queue(new GaveTwiceMail($claim, $gift));
    }
}

<?php

namespace App\Listeners;

use App\Events\GiftClaimed;
use App\Jobs\SendReservationReceipt;

class SendGaveTwiceEmail
{
    public function handle(GiftClaimed $event): void
    {
        $claim = $event->claim;

        $email = $claim->claimer_email ?? $claim->user?->email;

        if (! $email) {
            return;
        }

        SendReservationReceipt::request($claim, app()->getLocale());
    }
}

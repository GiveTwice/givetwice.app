<?php

namespace App\Listeners;

use App\Events\GiftClaimed;
use App\Jobs\SendReservationReceipt;

class RequestReservationReceipt
{
    public function handle(GiftClaimed $event): void
    {
        $claim = $event->claim;

        $email = $claim->recipientEmail();

        if (! $email) {
            return;
        }

        SendReservationReceipt::request($claim, app()->getLocale());
    }
}

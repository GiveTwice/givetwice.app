<?php

namespace App\Listeners;

use App\Events\GiftClaimed;
use Spatie\SlackAlerts\Facades\SlackAlert;

class SendGiftClaimedSlackNotification
{
    public function handle(GiftClaimed $event): void
    {
        $gift = $event->gift;
        $claim = $event->claim;

        $claimer = $claim->user
            ? $claim->user->email
            : $claim->claimer_email;

        $owner = $gift->user->email;

        SlackAlert::message("🎉 {$claimer} claimed \"{$gift->title}\" from {$owner}'s list");
    }
}

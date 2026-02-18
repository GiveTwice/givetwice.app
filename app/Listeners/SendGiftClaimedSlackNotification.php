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
            ? '<'.route('admin.users.show', $claim->user).'|'.$claim->user->email.'>'
            : $claim->claimer_email;

        $ownerUrl = route('admin.users.show', $gift->user);
        $giftUrl = route('admin.gifts.show', $gift);

        SlackAlert::message("🎉 {$claimer} claimed <{$giftUrl}|\"{$gift->title}\"> from <{$ownerUrl}|{$gift->user->email}>'s list");
    }
}

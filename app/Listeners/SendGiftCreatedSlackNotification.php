<?php

namespace App\Listeners;

use App\Events\GiftCreated;
use Spatie\SlackAlerts\Facades\SlackAlert;

class SendGiftCreatedSlackNotification
{
    public function handle(GiftCreated $event): void
    {
        $gift = $event->gift;
        $title = $gift->title ?: $gift->url;
        $adminGiftUrl = route('admin.gifts.show', $gift);
        $adminUserUrl = route('admin.users.show', $gift->user);

        SlackAlert::message("🎁 <{$adminUserUrl}|{$gift->user->email}> added a gift: <{$adminGiftUrl}|{$title}>");
    }
}

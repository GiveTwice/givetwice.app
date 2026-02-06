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

        SlackAlert::message("🎁 {$gift->user->email} added a gift: {$title}");
    }
}

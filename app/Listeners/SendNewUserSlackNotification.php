<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Spatie\SlackAlerts\Facades\SlackAlert;

class SendNewUserSlackNotification
{
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $provider = $user->google_id ? 'Google' : ($user->facebook_id ? 'Facebook' : 'email');

        $adminUrl = route('admin.users.show', $user);

        SlackAlert::message("👤 New user registered: <{$adminUrl}|{$user->email}> (via {$provider})");
    }
}

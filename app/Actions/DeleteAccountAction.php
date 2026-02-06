<?php

namespace App\Actions;

use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\SlackAlerts\Facades\SlackAlert;

class DeleteAccountAction
{
    public function execute(User $user): void
    {
        $email = $user->email;
        $userId = $user->id;

        foreach ($user->gifts as $gift) {
            /** @var Gift $gift */
            $gift->clearMediaCollection('image');
        }

        $user->delete();

        DB::table('sessions')
            ->where('user_id', $userId)
            ->delete();

        SlackAlert::message("👋 {$email} deleted their account");
    }
}

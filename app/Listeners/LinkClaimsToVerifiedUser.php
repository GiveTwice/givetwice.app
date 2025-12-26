<?php

namespace App\Listeners;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class LinkClaimsToVerifiedUser
{
    public function handle(Verified $event): void
    {
        /** @var User $user */
        $user = $event->user;

        Claim::query()
            ->whereNull('user_id')
            ->whereRaw('LOWER(claimer_email) = ?', [strtolower($user->email)])
            ->update(['user_id' => $user->id]);
    }
}

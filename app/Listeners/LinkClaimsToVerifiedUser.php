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

        $claims = Claim::query()
            ->whereNull('user_id')
            ->where('claimer_email', $user->email)
            ->with('gift.lists.users')
            ->get();

        foreach ($claims as $claim) {
            $claim->update(['user_id' => $user->id]);

            if ($claim->gift) {
                foreach ($claim->gift->lists as $list) {
                    $user->followListIfEligible($list);
                }
            }
        }
    }
}

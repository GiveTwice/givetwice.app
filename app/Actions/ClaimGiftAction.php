<?php

namespace App\Actions;

use App\Events\GiftClaimed;
use App\Exceptions\Claim\AlreadyClaimedException;
use App\Exceptions\Claim\CannotClaimOwnGiftException;
use App\Exceptions\Claim\UserAlreadyClaimedException;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\User;

class ClaimGiftAction
{
    public function execute(Gift $gift, User $user): Claim
    {
        if ($gift->user_id === $user->id) {
            throw new CannotClaimOwnGiftException;
        }

        if ($gift->isClaimed()) {
            throw new AlreadyClaimedException;
        }

        $existingClaim = $gift->claims()->where('user_id', $user->id)->first();
        if ($existingClaim) {
            throw new UserAlreadyClaimedException;
        }

        $claim = Claim::create([
            'gift_id' => $gift->id,
            'user_id' => $user->id,
            'confirmed_at' => now(),
        ]);

        $gift->load('lists');
        event(new GiftClaimed($gift, $claim));

        return $claim;
    }
}

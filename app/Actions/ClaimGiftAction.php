<?php

namespace App\Actions;

use App\Events\GiftClaimed;
use App\Exceptions\ClaimException;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\User;

class ClaimGiftAction
{
    public function execute(Gift $gift, User $user, ?string $notes = null): Claim
    {
        if ($gift->user_id === $user->id) {
            throw ClaimException::cannotClaimOwnGift();
        }

        if ($gift->isClaimed()) {
            throw ClaimException::alreadyClaimed();
        }

        $existingClaim = $gift->claims()->where('user_id', $user->id)->first();
        if ($existingClaim) {
            throw ClaimException::userAlreadyClaimed();
        }

        $claim = Claim::create([
            'gift_id' => $gift->id,
            'user_id' => $user->id,
            'confirmed_at' => now(),
            'notes' => $notes,
        ]);

        $gift->load('lists');
        event(new GiftClaimed($gift, $claim));

        return $claim;
    }
}

<?php

namespace App\Actions;

use App\Events\GiftClaimed;
use App\Exceptions\Claim\AlreadyClaimedException;
use App\Exceptions\Claim\CannotClaimOwnGiftException;
use App\Exceptions\Claim\UserAlreadyClaimedException;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClaimGiftAction
{
    public function execute(Gift $gift, User $user): Claim
    {
        if ($gift->user_id === $user->id) {
            throw new CannotClaimOwnGiftException;
        }

        return DB::transaction(function () use ($gift, $user) {
            $lockedGift = Gift::lockForUpdate()->find($gift->id);

            if ($lockedGift->isClaimed()) {
                throw new AlreadyClaimedException;
            }

            $existingClaim = $lockedGift->claims()->where('user_id', $user->id)->first();
            if ($existingClaim) {
                throw new UserAlreadyClaimedException;
            }

            $claim = Claim::create([
                'gift_id' => $lockedGift->id,
                'user_id' => $user->id,
                'confirmed_at' => now(),
            ]);

            $lockedGift->load('lists.users');

            foreach ($lockedGift->lists as $list) {
                $user->followListIfEligible($list);
            }

            event(new GiftClaimed($lockedGift, $claim));

            return $claim;
        });
    }
}

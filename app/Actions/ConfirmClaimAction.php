<?php

namespace App\Actions;

use App\Events\GiftClaimed;
use App\Exceptions\Claim\AlreadyClaimedException;
use App\Exceptions\Claim\InvalidTokenException;
use App\Models\Claim;
use App\Models\Gift;
use Illuminate\Support\Facades\DB;

class ConfirmClaimAction
{
    public function execute(string $token): Claim
    {
        $claim = Claim::where('confirmation_token', $token)
            ->whereNull('confirmed_at')
            ->first();

        if (! $claim) {
            throw new InvalidTokenException;
        }

        $result = DB::transaction(function () use ($claim) {
            $lockedGift = Gift::lockForUpdate()->find($claim->gift_id);

            if ($lockedGift->isClaimed()) {
                $claim->delete();

                return null;
            }

            $lockedGift->load('lists');
            $claim->confirm();

            $claimCount = $lockedGift->claims()->whereNotNull('confirmed_at')->count();

            return ['claim' => $claim, 'gift' => $lockedGift, 'claim_count' => $claimCount];
        });

        if ($result === null) {
            throw new AlreadyClaimedException;
        }

        event(new GiftClaimed($result['gift'], $result['claim'], $result['claim_count']));

        return $result['claim'];
    }
}

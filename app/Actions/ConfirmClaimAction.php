<?php

namespace App\Actions;

use App\Exceptions\Claim\AlreadyClaimedException;
use App\Exceptions\Claim\InvalidTokenException;
use App\Models\Claim;

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

        /** @var \App\Models\Gift $gift */
        $gift = $claim->gift;

        if ($gift->isClaimed()) {
            $claim->delete();
            throw new AlreadyClaimedException;
        }

        $claim->confirm();

        return $claim;
    }
}

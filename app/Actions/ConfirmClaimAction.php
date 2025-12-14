<?php

namespace App\Actions;

use App\Exceptions\ClaimException;
use App\Models\Claim;

class ConfirmClaimAction
{
    public function execute(string $token): Claim
    {
        $claim = Claim::where('confirmation_token', $token)
            ->whereNull('confirmed_at')
            ->first();

        if (! $claim) {
            throw ClaimException::invalidToken();
        }

        /** @var \App\Models\Gift $gift */
        $gift = $claim->gift;

        // Race condition: gift may have been claimed while confirmation email was pending
        if ($gift->isClaimed()) {
            $claim->delete();
            throw ClaimException::alreadyClaimed();
        }

        $claim->confirm();

        return $claim;
    }
}

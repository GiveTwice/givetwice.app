<?php

namespace App\Actions;

use App\Events\GiftClaimed;
use App\Exceptions\Claim\AlreadyClaimedException;
use App\Exceptions\Claim\EmailAlreadyClaimedException;
use App\Models\Claim;
use App\Models\Gift;
use Illuminate\Support\Facades\DB;

class ReserveGiftAction
{
    public function execute(Gift $gift, string $email): Claim
    {
        $claim = DB::transaction(function () use ($gift, $email) {
            $lockedGift = Gift::query()->lockForUpdate()->findOrFail($gift->id);
            if ($lockedGift->isClaimed()) {
                throw new AlreadyClaimedException;
            }
            if ($lockedGift->claims()->where('claimer_email', strtolower(trim($email)))->whereNotNull('confirmed_at')->exists()) {
                throw new EmailAlreadyClaimedException;
            }
            $claim = $lockedGift->claims()->create([
                'claimer_email' => strtolower(trim($email)),
                'confirmed_at' => now(),
            ]);

            return $claim;
        });
        event(new GiftClaimed($gift, $claim, $gift->getConfirmedClaimCount()));

        return $claim;
    }
}

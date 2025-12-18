<?php

namespace App\Actions;

use App\Exceptions\Claim\AlreadyClaimedException;
use App\Exceptions\Claim\ConfirmationResentException;
use App\Exceptions\Claim\EmailAlreadyClaimedException;
use App\Mail\ClaimConfirmationMail;
use App\Models\Claim;
use App\Models\Gift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CreatePendingClaimAction
{
    public function execute(Gift $gift, string $email, ?string $name = null): Claim
    {
        /** @var Claim|null $existingClaim */
        $existingClaim = $gift->claims()
            ->where('claimer_email', $email)
            ->first();

        if ($existingClaim) {
            if ($existingClaim->isConfirmed()) {
                throw new EmailAlreadyClaimedException;
            }

            Mail::to($email)->send(new ClaimConfirmationMail($existingClaim));
            throw new ConfirmationResentException;
        }

        return DB::transaction(function () use ($gift, $email, $name) {
            $lockedGift = Gift::lockForUpdate()->find($gift->id);

            if ($lockedGift->isClaimed()) {
                throw new AlreadyClaimedException;
            }

            $claim = Claim::create([
                'gift_id' => $lockedGift->id,
                'claimer_email' => $email,
                'claimer_name' => $name,
            ]);

            Mail::to($email)->send(new ClaimConfirmationMail($claim));

            return $claim;
        });
    }
}

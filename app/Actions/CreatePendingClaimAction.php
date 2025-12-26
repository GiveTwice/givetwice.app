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
    public function execute(Gift $gift, string $email, ?string $name = null, ?string $locale = null): Claim
    {
        $locale ??= app()->getLocale();
        $normalizedEmail = strtolower($email);

        /** @var Claim|null $existingClaim */
        $existingClaim = $gift->claims()
            ->where('claimer_email', $normalizedEmail)
            ->first();

        if ($existingClaim) {
            if ($existingClaim->isConfirmed()) {
                throw new EmailAlreadyClaimedException;
            }

            Mail::to($normalizedEmail)->send(new ClaimConfirmationMail($existingClaim, $locale));
            throw new ConfirmationResentException;
        }

        return DB::transaction(function () use ($gift, $normalizedEmail, $name, $locale) {
            $lockedGift = Gift::lockForUpdate()->find($gift->id);

            if ($lockedGift->isClaimed()) {
                throw new AlreadyClaimedException;
            }

            $claim = Claim::create([
                'gift_id' => $lockedGift->id,
                'claimer_email' => $normalizedEmail,
                'claimer_name' => $name,
            ]);

            Mail::to($normalizedEmail)->send(new ClaimConfirmationMail($claim, $locale));

            return $claim;
        });
    }
}

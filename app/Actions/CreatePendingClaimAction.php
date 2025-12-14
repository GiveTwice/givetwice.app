<?php

namespace App\Actions;

use App\Exceptions\ClaimException;
use App\Mail\ClaimConfirmationMail;
use App\Models\Claim;
use App\Models\Gift;
use Illuminate\Support\Facades\Mail;

class CreatePendingClaimAction
{
    public function execute(Gift $gift, string $email, ?string $name = null): Claim
    {
        if ($gift->isClaimed()) {
            throw ClaimException::alreadyClaimed();
        }

        $existingClaim = $gift->claims()
            ->where('claimer_email', $email)
            ->first();

        if ($existingClaim) {
            /** @var Claim $existingClaim */
            if ($existingClaim->isConfirmed()) {
                throw ClaimException::emailAlreadyClaimed();
            }

            Mail::to($email)->send(new ClaimConfirmationMail($existingClaim));
            throw ClaimException::confirmationResent();
        }

        $claim = Claim::create([
            'gift_id' => $gift->id,
            'claimer_email' => $email,
            'claimer_name' => $name,
        ]);

        Mail::to($email)->send(new ClaimConfirmationMail($claim));

        return $claim;
    }
}

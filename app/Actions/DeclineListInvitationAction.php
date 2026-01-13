<?php

namespace App\Actions;

use App\Actions\Concerns\ValidatesListInvitation;
use App\Exceptions\ListInvitation\InvalidInvitationException;
use App\Models\ListInvitation;
use App\Models\User;

class DeclineListInvitationAction
{
    use ValidatesListInvitation;

    public function execute(string $token, User $user): void
    {
        $invitation = ListInvitation::where('token', $token)->first();

        if (! $invitation) {
            throw new InvalidInvitationException;
        }

        $this->validateInvitationForUser($invitation, $user);

        $invitation->decline();
    }
}

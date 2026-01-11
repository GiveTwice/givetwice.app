<?php

namespace App\Actions;

use App\Exceptions\ListInvitation\InvalidInvitationException;
use App\Models\ListInvitation;
use App\Models\User;

class DeclineListInvitationAction
{
    public function execute(string $token, User $user): void
    {
        $invitation = ListInvitation::where('token', $token)->first();

        if (! $invitation || ! $invitation->isPending()) {
            throw new InvalidInvitationException;
        }

        if ($invitation->invitee_id && $invitation->invitee_id !== $user->id) {
            throw new InvalidInvitationException;
        }

        if (strtolower($invitation->email) !== strtolower($user->email)) {
            throw new InvalidInvitationException;
        }

        $invitation->decline();
    }
}

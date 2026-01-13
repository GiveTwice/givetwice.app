<?php

namespace App\Actions\Concerns;

use App\Exceptions\ListInvitation\InvalidInvitationException;
use App\Exceptions\ListInvitation\InvitationExpiredException;
use App\Models\ListInvitation;
use App\Models\User;

trait ValidatesListInvitation
{
    protected function validateInvitationForUser(ListInvitation $invitation, User $user): void
    {
        if ($invitation->isExpired()) {
            throw new InvitationExpiredException;
        }

        if (! $invitation->isPending()) {
            throw new InvalidInvitationException;
        }

        if ($invitation->invitee_id && $invitation->invitee_id !== $user->id) {
            throw new InvalidInvitationException;
        }

        if ($invitation->email !== $user->email) {
            throw new InvalidInvitationException;
        }
    }
}

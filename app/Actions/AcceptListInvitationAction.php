<?php

namespace App\Actions;

use App\Exceptions\ListInvitation\InvalidInvitationException;
use App\Exceptions\ListInvitation\InvitationExpiredException;
use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptListInvitationAction
{
    public function execute(string $token, User $user): GiftList
    {
        $invitation = ListInvitation::where('token', $token)->first();

        if (! $invitation) {
            throw new InvalidInvitationException;
        }

        if ($invitation->isExpired()) {
            throw new InvitationExpiredException;
        }

        if (! $invitation->isPending()) {
            throw new InvalidInvitationException;
        }

        if ($invitation->invitee_id && $invitation->invitee_id !== $user->id) {
            throw new InvalidInvitationException;
        }

        if (strtolower($invitation->email) !== strtolower($user->email)) {
            throw new InvalidInvitationException;
        }

        return DB::transaction(function () use ($invitation, $user) {
            $invitation->accept();

            if (! $invitation->list->hasUser($user)) {
                $invitation->list->users()->attach($user->id, ['joined_at' => now()]);
            }

            if (! $invitation->invitee_id) {
                $invitation->update(['invitee_id' => $user->id]);
            }

            return $invitation->list;
        });
    }
}

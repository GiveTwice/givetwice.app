<?php

namespace App\Actions;

use App\Events\ListInvitationCreated;
use App\Exceptions\ListInvitation\AlreadyCollaboratorException;
use App\Exceptions\ListInvitation\CannotInviteSelfException;
use App\Exceptions\ListInvitation\InvitationAlreadyPendingException;
use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InviteToListAction
{
    public function execute(GiftList $list, User $inviter, string $email): ListInvitation
    {
        $normalizedEmail = strtolower(trim($email));

        if ($inviter->email === $normalizedEmail) {
            throw new CannotInviteSelfException;
        }

        $existingUser = User::where('email', $normalizedEmail)->first();

        if ($existingUser && $list->hasUser($existingUser)) {
            throw new AlreadyCollaboratorException;
        }

        $pendingInvitation = $list->pendingInvitations()
            ->where('email', $normalizedEmail)
            ->first();

        if ($pendingInvitation) {
            throw new InvitationAlreadyPendingException;
        }

        return DB::transaction(function () use ($list, $inviter, $normalizedEmail, $existingUser) {
            $list->invitations()
                ->where('email', $normalizedEmail)
                ->where(fn ($q) => $q->whereNotNull('declined_at')->orWhere('expires_at', '<', now()))
                ->delete();

            $invitation = ListInvitation::create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => $existingUser?->id,
                'email' => $normalizedEmail,
            ]);

            event(new ListInvitationCreated($invitation));

            return $invitation;
        });
    }
}

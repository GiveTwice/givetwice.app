<?php

namespace App\Actions;

use App\Actions\Concerns\ValidatesListInvitation;
use App\Exceptions\ListInvitation\InvalidInvitationException;
use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcceptListInvitationAction
{
    use ValidatesListInvitation;

    public function execute(string $token, User $user): GiftList
    {
        $invitation = ListInvitation::where('token', $token)->first();

        if (! $invitation) {
            throw new InvalidInvitationException;
        }

        $this->validateInvitationForUser($invitation, $user);

        /** @var GiftList $list */
        $list = DB::transaction(function () use ($invitation, $user): GiftList {
            $invitation->accept();

            if (! $invitation->list->hasUser($user)) {
                $invitation->list->users()->attach($user->id, ['joined_at' => now()]);
            }

            if (! $invitation->invitee_id) {
                $invitation->update(['invitee_id' => $user->id]);
            }

            return $invitation->list;
        });

        return $list;
    }
}

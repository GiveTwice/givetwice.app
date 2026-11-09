<?php

namespace App\Listeners;

use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class LinkInvitationsToVerifiedUser
{
    public function handle(Verified $event): void
    {
        /** @var User $user */
        $user = $event->user;

        ListInvitation::query()
            ->whereNull('invitee_id')
            ->where('email', $user->email)
            ->pending()
            ->update(['invitee_id' => $user->id]);
    }
}

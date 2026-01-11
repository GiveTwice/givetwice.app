<?php

namespace App\Exceptions\ListInvitation;

class InvitationAlreadyPendingException extends ListInvitationException
{
    public function __construct()
    {
        parent::__construct(__('An invitation is already pending for this email.'));
    }
}

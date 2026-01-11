<?php

namespace App\Exceptions\ListInvitation;

class InvalidInvitationException extends ListInvitationException
{
    public function __construct()
    {
        parent::__construct(__('Invalid or expired invitation.'));
    }
}

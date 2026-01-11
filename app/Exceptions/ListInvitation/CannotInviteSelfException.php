<?php

namespace App\Exceptions\ListInvitation;

class CannotInviteSelfException extends ListInvitationException
{
    public function __construct()
    {
        parent::__construct(__('You cannot invite yourself.'));
    }
}

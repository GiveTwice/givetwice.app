<?php

namespace App\Exceptions\ListInvitation;

class InvitationExpiredException extends ListInvitationException
{
    public function __construct()
    {
        parent::__construct(__('This invitation has expired.'));
    }
}

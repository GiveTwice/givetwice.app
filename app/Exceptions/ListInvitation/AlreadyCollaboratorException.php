<?php

namespace App\Exceptions\ListInvitation;

class AlreadyCollaboratorException extends ListInvitationException
{
    public function __construct()
    {
        parent::__construct(__('This user is already a collaborator.'));
    }
}

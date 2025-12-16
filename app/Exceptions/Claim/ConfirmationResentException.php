<?php

namespace App\Exceptions\Claim;

class ConfirmationResentException extends ClaimException
{
    public bool $isResent = true;

    public function __construct()
    {
        parent::__construct(__('A confirmation email has been resent to your email address.'));
    }
}

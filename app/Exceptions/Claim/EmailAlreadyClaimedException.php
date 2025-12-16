<?php

namespace App\Exceptions\Claim;

class EmailAlreadyClaimedException extends ClaimException
{
    public function __construct()
    {
        parent::__construct(__('This gift has already been claimed with this email.'));
    }
}

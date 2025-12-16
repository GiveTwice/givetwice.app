<?php

namespace App\Exceptions\Claim;

class UserAlreadyClaimedException extends ClaimException
{
    public function __construct()
    {
        parent::__construct(__('You have already claimed this gift.'));
    }
}

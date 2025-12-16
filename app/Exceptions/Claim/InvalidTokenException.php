<?php

namespace App\Exceptions\Claim;

class InvalidTokenException extends ClaimException
{
    public function __construct()
    {
        parent::__construct(__('Invalid or expired confirmation link.'));
    }
}

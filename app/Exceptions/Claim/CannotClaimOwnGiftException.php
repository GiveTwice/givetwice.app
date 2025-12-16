<?php

namespace App\Exceptions\Claim;

class CannotClaimOwnGiftException extends ClaimException
{
    public function __construct()
    {
        parent::__construct(__('You cannot claim your own gift.'));
    }
}

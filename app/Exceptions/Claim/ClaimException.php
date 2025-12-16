<?php

namespace App\Exceptions\Claim;

use Exception;

abstract class ClaimException extends Exception
{
    public bool $isResent = false;
}

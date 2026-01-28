<?php

namespace App\Exceptions;

use Exception;

class FollowListException extends Exception
{
    public static function cannotFollowOwnList(): self
    {
        return new self(__('You cannot follow your own list'));
    }
}

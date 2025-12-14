<?php

namespace App\Exceptions;

use Exception;

class ClaimException extends Exception
{
    public bool $isResent = false;

    public static function cannotClaimOwnGift(): self
    {
        return new self(__('You cannot claim your own gift.'));
    }

    public static function alreadyClaimed(): self
    {
        return new self(__('This gift has already been claimed.'));
    }

    public static function userAlreadyClaimed(): self
    {
        return new self(__('You have already claimed this gift.'));
    }

    public static function emailAlreadyClaimed(): self
    {
        return new self(__('This gift has already been claimed with this email.'));
    }

    public static function confirmationResent(): self
    {
        $exception = new self(__('A confirmation email has been resent to your email address.'));
        $exception->isResent = true;

        return $exception;
    }

    public static function invalidToken(): self
    {
        return new self(__('Invalid or expired confirmation link.'));
    }
}

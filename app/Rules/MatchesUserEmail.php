<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MatchesUserEmail implements ValidationRule
{
    public function __construct(
        protected User $user,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtolower($value) !== strtolower($this->user->email)) {
            $fail(__('The email confirmation does not match your account email.'));
        }
    }
}

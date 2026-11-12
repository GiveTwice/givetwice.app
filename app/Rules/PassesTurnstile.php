<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class PassesTurnstile implements ValidationRule
{
    /**
     * Runs the rule even when the form sent no token, so a bot that skips the widget fails too.
     */
    public bool $implicit = true;

    /**
     * Asks Cloudflare whether the Turnstile token from the sign-up form is genuine.
     *
     * When Cloudflare cannot be reached, the sign-up fails rather than letting a bot through.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $token = is_string($value) ? $value : '';

        $passed = $token !== '' && rescue(fn (): bool => Http::asForm()
            ->timeout(5)
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => request()->ip(),
            ])
            ->json('success') === true, false);

        if ($passed) {
            return;
        }

        $fail(__('We could not check that you are human. Give it another try.'));
    }
}

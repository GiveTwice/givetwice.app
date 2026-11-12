<?php

namespace App\Actions\Fortify;

use App\Enums\SupportedLocale;
use App\Helpers\OccasionHelper;
use App\Models\User;
use App\Rules\PassesTurnstile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Symfony\Component\HttpFoundation\IpUtils;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        if (! config('app.allow_registration')) {
            abort(403, 'Registration is currently disabled.');
        }

        $limits = $this->registrationLimits();

        if ($limits->contains(fn (array $limit): bool => RateLimiter::tooManyAttempts($limit['key'], $limit['max']))) {
            throw ValidationException::withMessages([
                'email' => __('That is a lot of new accounts from one connection. Give it a while and try again.'),
            ]);
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'cf-turnstile-response' => Rule::when(filled(config('services.turnstile.secret_key')), [new PassesTurnstile]),
        ])->validate();

        $occasion = $input['occasion'] ?? null;
        if ($occasion && OccasionHelper::get($occasion)) {
            session(['registration_occasion' => $occasion]);
        }

        $user = User::create([
            'name' => $input['name'],
            'email' => strtolower($input['email']),
            'password' => $input['password'],
            'locale_preference' => $this->detectLocale(),
        ]);

        $limits->each(fn (array $limit) => RateLimiter::hit($limit['key'], $limit['decaySeconds']));

        return $user;
    }

    /**
     * Returns the sign-up limits for the connection making this request.
     *
     * Bots sign up other people's addresses here, so that each victim gets our verification mail.
     * Only accounts that were actually created count, so a typo in the form never locks anyone out.
     * An IPv6 address counts per /64, because one subscriber can rotate through a whole /64 for free.
     *
     * @return Collection<int, array{key: string, max: int, decaySeconds: int}>
     */
    private function registrationLimits(): Collection
    {
        $network = IpUtils::anonymize((string) request()->ip(), v4Bytes: 0, v6Bytes: 8);

        return collect([
            ['key' => 'register:hour:'.$network, 'max' => 3, 'decaySeconds' => 3600],
            ['key' => 'register:day:'.$network, 'max' => 5, 'decaySeconds' => 86400],
        ]);
    }

    private function detectLocale(): string
    {
        $referer = request()->headers->get('referer', '');
        if (preg_match('#/([a-z]{2})/(register|login)#', $referer, $matches)) {
            $locale = $matches[1];
            if (SupportedLocale::isSupported($locale)) {
                return $locale;
            }
        }

        return config('app.locale', 'en');
    }
}

<?php

namespace App\Actions\Fortify;

use App\Enums\SupportedLocale;
use App\Helpers\OccasionHelper;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

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
        ])->validate();

        $occasion = $input['occasion'] ?? null;
        if ($occasion && OccasionHelper::get($occasion)) {
            session(['registration_occasion' => $occasion]);
        }

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'locale_preference' => $this->detectLocale(),
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

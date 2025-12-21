<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        session(['social_auth_locale' => app()->getLocale()]);

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): \Illuminate\Http\RedirectResponse
    {
        return $this->handleSocialCallback('google', 'google_id');
    }

    public function redirectToFacebook(): RedirectResponse
    {
        session(['social_auth_locale' => app()->getLocale()]);

        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback(): \Illuminate\Http\RedirectResponse
    {
        return $this->handleSocialCallback('facebook', 'facebook_id');
    }

    public function redirectToApple(): RedirectResponse
    {
        session(['social_auth_locale' => app()->getLocale()]);

        /** @var \SocialiteProviders\Apple\Provider $driver */
        $driver = Socialite::driver('apple');

        return $driver
            ->scopes(['name', 'email'])
            ->redirect();
    }

    public function handleAppleCallback(): \Illuminate\Http\RedirectResponse
    {
        return $this->handleSocialCallback('apple', 'apple_id');
    }

    protected function handleSocialCallback(string $provider, string $socialIdField): \Illuminate\Http\RedirectResponse
    {
        $locale = session()->pull('social_auth_locale', app()->getLocale());

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('home', ['locale' => $locale])
                ->with('error', __('Unable to authenticate with :provider.', ['provider' => ucfirst($provider)]));
        }

        // Check if user exists with this social ID
        $user = User::where($socialIdField, $socialUser->getId())->first();

        if ($user) {
            Auth::login($user, true);

            return $this->redirectToDashboard();
        }

        $email = $socialUser->getEmail();

        if (! $email) {
            return redirect()->route('home', ['locale' => $locale])
                ->with('error', __('Unable to authenticate with :provider.', ['provider' => ucfirst($provider)]));
        }

        // Check if user exists with this email
        $user = User::where('email', $email)->first();

        if ($user) {
            // Link social account to existing user
            $updateData = [
                $socialIdField => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ];

            // Social auth implies email is verified (provider verified it)
            if (! $user->email_verified_at) {
                $updateData['email_verified_at'] = now();
            }

            $user->update($updateData);

            Auth::login($user, true);

            return $this->redirectToDashboard();
        }

        // Create new user (only if registration is allowed)
        if (! config('app.allow_registration')) {
            return redirect()->route('home', ['locale' => $locale])
                ->with('error', __('Registration is currently disabled.'));
        }

        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';

        $user = User::create([
            'name' => $name,
            'email' => $email,
            $socialIdField => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(),
            'locale_preference' => $locale,
        ]);

        event(new Registered($user));

        Auth::login($user, true);

        return $this->redirectToDashboard();
    }

    protected function redirectToDashboard(): \Illuminate\Http\RedirectResponse
    {
        $locale = auth()->user()->locale_preference ?? app()->getLocale();

        return redirect("/{$locale}/dashboard");
    }
}

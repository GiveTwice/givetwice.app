<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\OccasionHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        $this->storeSessionDataForCallback();

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        return $this->handleSocialCallback('google', 'google_id');
    }

    public function redirectToFacebook(): RedirectResponse
    {
        $this->storeSessionDataForCallback();

        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback(): RedirectResponse
    {
        return $this->handleSocialCallback('facebook', 'facebook_id');
    }

    private function storeSessionDataForCallback(): void
    {
        session(['social_auth_locale' => app()->getLocale()]);

        $occasion = request('occasion');
        if ($occasion && OccasionHelper::get($occasion)) {
            session(['registration_occasion' => $occasion]);
        }
    }

    protected function handleSocialCallback(string $provider, string $socialIdField): RedirectResponse
    {
        $locale = session()->pull('social_auth_locale', app()->getLocale());
        $occasion = session()->pull('registration_occasion');

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('home', ['locale' => $locale])
                ->with('error', __('Unable to authenticate with :provider.', ['provider' => ucfirst($provider)]));
        }

        $user = User::where($socialIdField, $socialUser->getId())->first();

        if ($user) {
            Auth::login($user, true);

            return $this->redirectToDashboard();
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            $updateData = [
                $socialIdField => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ];

            if (! $user->email_verified_at) {
                $updateData['email_verified_at'] = now();
            }

            $user->update($updateData);

            Auth::login($user, true);

            return $this->redirectToDashboard();
        }

        if (! config('app.allow_registration')) {
            return redirect()->route('home', ['locale' => $locale])
                ->with('error', __('Registration is currently disabled.'));
        }

        if ($occasion) {
            session(['registration_occasion' => $occasion]);
        }

        $user = User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            $socialIdField => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(),
            'locale_preference' => $locale,
        ]);

        event(new Registered($user));

        Auth::login($user, true);

        return $this->redirectToDashboard();
    }

    protected function redirectToDashboard(): RedirectResponse
    {
        $locale = auth()->user()->locale_preference ?? app()->getLocale();

        return redirect("/{$locale}/dashboard");
    }
}

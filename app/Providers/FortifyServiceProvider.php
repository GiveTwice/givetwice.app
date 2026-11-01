<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\LogoutResponse;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Social auth users have no password — skip password confirmation for them
        Fortify::confirmPasswordsUsing(function ($user, ?string $password) {
            if (is_null($user->password)) {
                return true;
            }

            return Hash::check($password, $user->password);
        });

        $resetPasswordUrl = function (User $notifiable, string $token): string {
            $locale = $notifiable->locale_preference ?? config('app.locale', 'en');

            return route('password.reset', [
                'locale' => $locale,
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        };
        ResetPassword::createUrlUsing($resetPasswordUrl);
        ResetPassword::toMailUsing(function (User $user, string $token) use ($resetPasswordUrl): MailMessage {
            return (new MailMessage)
                ->subject(__('Reset your password'))
                ->view(['emails.authentication', 'emails.text.authentication'], [
                    'user' => $user,
                    'title' => __('Reset your password'),
                    'intro' => __('You are receiving this email because we received a password reset request for your account.'),
                    'actionText' => __('Reset Password'),
                    'actionUrl' => $resetPasswordUrl($user, $token),
                    'outroLines' => [
                        __('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]),
                        __('If you did not request a password reset, no further action is required.'),
                    ],
                ]);
        });

        $verificationUrl = function (User $notifiable): string {
            $locale = $notifiable->locale_preference ?? config('app.locale', 'en');

            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'locale' => $locale,
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            return $verificationUrl;
        };
        VerifyEmail::createUrlUsing($verificationUrl);
        VerifyEmail::toMailUsing(function (User $user, string $url): MailMessage {
            return (new MailMessage)
                ->subject(__('Verify your email address'))
                ->view(['emails.authentication', 'emails.text.authentication'], [
                    'user' => $user,
                    'title' => __('Verify your email address'),
                    'intro' => __('Please click the button below to verify your email address.'),
                    'actionText' => __('Verify Email Address'),
                    'actionUrl' => $url,
                    'outroLines' => [__('If you did not create an account, no further action is required.')],
                ]);
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Configure views
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
    }
}

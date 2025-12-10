<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

// Redirect root to detected locale
Route::get('/', function () {
    $locale = SetLocale::detectBrowserLocale(request());
    return redirect("/{$locale}", 302);
});

// Redirect /dashboard to locale-prefixed dashboard
Route::get('/dashboard', function () {
    $locale = auth()->user()?->locale_preference ?? app()->getLocale();
    return redirect("/{$locale}/dashboard");
})->middleware('auth')->name('dashboard');

// Locale-prefixed routes
Route::prefix('{locale}')
    ->where(['locale' => 'en|nl|fr'])
    ->middleware('locale')
    ->group(function () {
        // Public routes
        Route::get('/', function () {
            return view('home');
        })->name('home');

        // Auth view routes (GET only - POST handled by Fortify)
        Route::middleware('guest')->group(function () {
            Route::get('/login', function () {
                return view('auth.login');
            })->name('login');

            Route::get('/register', function () {
                return view('auth.register');
            })->name('register');

            Route::get('/forgot-password', function () {
                return view('auth.forgot-password');
            })->name('password.request');

            Route::get('/reset-password/{token}', function ($locale, $token) {
                return view('auth.reset-password', ['token' => $token, 'email' => request('email')]);
            })->name('password.reset');
        });

        // Email verification routes
        Route::middleware('auth')->group(function () {
            Route::get('/verify-email', function () {
                return view('auth.verify-email');
            })->name('verification.notice');

            Route::get('/confirm-password', function () {
                return view('auth.confirm-password');
            })->name('password.confirm');
        });

        // Social auth routes
        Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
        Route::get('/auth/facebook', [SocialAuthController::class, 'redirectToFacebook'])->name('auth.facebook');
        Route::get('/auth/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback']);

        // Protected routes
        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('/dashboard', function () {
                return view('dashboard');
            })->name('dashboard.locale');
        });
    });

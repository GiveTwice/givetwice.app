<?php

use App\Enums\SupportedLocale;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\PublicListController;
use App\Http\Controllers\SettingsController;
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

// Two-factor authentication challenge (no locale prefix - matches Fortify's POST route)
Route::get('/two-factor-challenge', function () {
    return view('auth.two-factor-challenge');
})->middleware('web')->name('two-factor.login');

// Locale-prefixed routes
Route::prefix('{locale}')
    ->where(['locale' => SupportedLocale::routePattern()])
    ->middleware('locale')
    ->group(function () {
        // Public routes
        Route::get('/', function () {
            return view('home');
        })->name('home');

        // Static pages
        Route::get('/about', function () {
            return view('pages.about');
        })->name('about');

        Route::get('/faq', function () {
            return view('pages.faq');
        })->name('faq');

        Route::get('/privacy', function () {
            return view('pages.privacy');
        })->name('privacy');

        Route::get('/terms', function () {
            return view('pages.terms');
        })->name('terms');

        Route::get('/transparency', function () {
            return view('pages.transparency');
        })->name('transparency');

        Route::get('/contact', function () {
            return view('pages.contact');
        })->name('contact');

        // Public list view (shareable)
        Route::get('/view/{list}', [PublicListController::class, 'show'])->name('public.list');

        // List show - handles both authenticated owners and redirects for guests/non-owners
        Route::get('/list/{list}', [ListController::class, 'show'])->name('list.show');

        // Claim routes (guest + authenticated)
        Route::get('/gifts/{gift}/claim', [ClaimController::class, 'showAnonymousForm'])->name('claim.anonymous.form');
        Route::post('/gifts/{gift}/claim-anonymous', [ClaimController::class, 'storeAnonymous'])->name('claim.anonymous.store');
        Route::get('/claim/confirm/{token}', [ClaimController::class, 'confirm'])->name('claim.confirm');
        Route::get('/gifts/{gift}/claimed/{token?}', [ClaimController::class, 'showConfirmed'])->name('claim.confirmed');

        // Gift card HTML (for real-time updates on public lists)
        Route::get('/view/{list}/gifts/{gift}/card', [GiftController::class, 'cardHtml'])->name('gifts.card-html');

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
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.locale');

            // List routes
            Route::get('/lists/create', [ListController::class, 'create'])->name('lists.create');
            Route::post('/lists', [ListController::class, 'store'])->name('lists.store');
            Route::get('/list/{list}/edit', [ListController::class, 'edit'])->name('list.edit');
            Route::put('/list/{list}', [ListController::class, 'update'])->name('list.update');
            Route::delete('/list/{list}', [ListController::class, 'destroy'])->name('list.destroy');

            // Gift routes
            Route::get('/gifts/create', [GiftController::class, 'create'])->name('gifts.create');
            Route::post('/gifts', [GiftController::class, 'store'])->name('gifts.store');
            Route::get('/gifts/{gift}/edit', [GiftController::class, 'edit'])->name('gifts.edit');
            Route::put('/gifts/{gift}', [GiftController::class, 'update'])->name('gifts.update');
            Route::delete('/gifts/{gift}', [GiftController::class, 'destroy'])->name('gifts.destroy');
            Route::post('/gifts/{gift}/refresh', [GiftController::class, 'refreshGiftDetails'])
                ->middleware('admin')
                ->name('gifts.refresh');
            Route::post('/gifts/{gift}/upload-image', [GiftController::class, 'uploadImage'])
                ->name('gifts.upload-image');

            // Claim routes (for registered users)
            Route::post('/gifts/{gift}/claim', [ClaimController::class, 'store'])->name('claim.store');
            Route::delete('/gifts/{gift}/claim', [ClaimController::class, 'destroy'])->name('claim.destroy');

            // Settings routes
            Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
            Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
            Route::post('/settings/profile/image', [SettingsController::class, 'uploadProfileImage'])
                ->middleware('throttle:10,1')
                ->name('settings.profile.image.upload');
            Route::delete('/settings/profile/image', [SettingsController::class, 'deleteProfileImage'])
                ->middleware('throttle:10,1')
                ->name('settings.profile.image.delete');
            Route::put('/settings/password', [SettingsController::class, 'updatePassword'])
                ->middleware('throttle:6,1')
                ->name('settings.password.update');
            Route::delete('/settings/sessions/{session}', [SettingsController::class, 'destroySession'])
                ->middleware('throttle:10,1')
                ->name('settings.sessions.destroy');
            Route::delete('/settings/sessions', [SettingsController::class, 'destroyAllSessions'])
                ->middleware('throttle:6,1')
                ->name('settings.sessions.destroy-all');
            Route::delete('/settings/account', [SettingsController::class, 'destroyAccount'])
                ->middleware('throttle:3,10')
                ->name('settings.account.destroy');
        });
    });

// Admin routes (English only, no locale prefix)
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('admin.users.show');
        Route::post('/users/{user}/toggle-admin', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle-admin');
    });

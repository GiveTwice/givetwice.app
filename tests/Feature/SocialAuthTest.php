<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function createMockedSocialiteUser(
    string $id = 'social-123',
    string $email = 'test@example.com',
    string $name = 'Test User',
    string $avatar = 'https://example.com/avatar.jpg'
): SocialiteUser {
    $mock = mock(SocialiteUser::class);
    $mock->shouldReceive('getId')->andReturn($id);
    $mock->shouldReceive('getEmail')->andReturn($email);
    $mock->shouldReceive('getName')->andReturn($name);
    $mock->shouldReceive('getAvatar')->andReturn($avatar);

    return $mock;
}

describe('SocialAuthController', function (): void {

    describe('redirect to provider', function (): void {

        it('stores locale in session when redirecting to Google', function (): void {
            $this->get('/nl/auth/google');

            expect(session('social_auth_locale'))->toBe('nl');
        });

        it('stores locale in session when redirecting to Facebook', function (): void {
            $this->get('/fr/auth/facebook');

            expect(session('social_auth_locale'))->toBe('fr');
        });

    });

    describe('new user registration', function (): void {

        it('creates user with locale preference from session', function (string $provider): void {
            $socialiteUser = createMockedSocialiteUser(email: 'newuser@example.com');
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $this->withSession(['social_auth_locale' => 'nl'])
                ->get("/en/auth/{$provider}/callback");

            $user = User::where('email', 'newuser@example.com')->first();

            expect($user)->not->toBeNull();
            expect($user->locale_preference)->toBe('nl');
        })->with(['google', 'facebook']);

        it('creates default list with localized name', function (string $locale, string $expectedListName): void {
            $email = "user-{$locale}@example.com";
            $socialiteUser = createMockedSocialiteUser(email: $email);
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $this->withSession(['social_auth_locale' => $locale])
                ->get('/en/auth/google/callback');

            $user = User::where('email', $email)->first();
            $list = $user->lists()->first();

            expect($list)->not->toBeNull();
            expect($list->name)->toBe($expectedListName);
        })->with([
            'English' => ['en', 'My wishlist'],
            'Dutch' => ['nl', 'Mijn verlanglijstje'],
            'French' => ['fr', 'Ma liste de souhaits'],
        ]);

        it('falls back to route locale when session has no stored locale', function (): void {
            $socialiteUser = createMockedSocialiteUser(email: 'fallback@example.com');
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $this->get('/en/auth/google/callback');

            $user = User::where('email', 'fallback@example.com')->first();

            expect($user->locale_preference)->toBe('en');
            expect($user->lists()->first()->name)->toBe('My wishlist');
        });

        it('logs user in after successful registration', function (): void {
            $socialiteUser = createMockedSocialiteUser(email: 'login@example.com');
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $this->withSession(['social_auth_locale' => 'nl'])
                ->get('/en/auth/google/callback');

            $this->assertAuthenticated();
            expect(auth()->user()->email)->toBe('login@example.com');
        });

        it('redirects to localized dashboard after registration', function (): void {
            $socialiteUser = createMockedSocialiteUser(email: 'redirect@example.com');
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $response = $this->withSession(['social_auth_locale' => 'nl'])
                ->get('/en/auth/google/callback');

            $response->assertRedirect('/nl/dashboard');
        });

        it('removes social auth locale from session after callback', function (): void {
            $socialiteUser = createMockedSocialiteUser(email: 'cleanup@example.com');
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $this->withSession(['social_auth_locale' => 'nl'])
                ->get('/en/auth/google/callback');

            expect(session()->has('social_auth_locale'))->toBeFalse();
        });

    });

    describe('existing user with matching social ID', function (): void {

        it('logs in without creating new user', function (): void {
            $existingUser = User::factory()->create([
                'google_id' => 'existing-google-123',
                'locale_preference' => 'fr',
            ]);

            $socialiteUser = createMockedSocialiteUser(
                id: 'existing-google-123',
                email: 'different@example.com'
            );
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $this->get('/en/auth/google/callback');

            $this->assertAuthenticatedAs($existingUser);
            expect(User::count())->toBe(1);
        });

        it('redirects to dashboard with user locale preference', function (): void {
            User::factory()->create([
                'google_id' => 'redirect-google-123',
                'locale_preference' => 'fr',
            ]);

            $socialiteUser = createMockedSocialiteUser(id: 'redirect-google-123');
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $response = $this->get('/en/auth/google/callback');

            $response->assertRedirect('/fr/dashboard');
        });

    });

    describe('existing user with matching email', function (): void {

        it('links social account to existing user', function (): void {
            $existingUser = User::factory()->create([
                'email' => 'existing@example.com',
                'google_id' => null,
            ]);

            $socialiteUser = createMockedSocialiteUser(
                id: 'new-google-id',
                email: 'existing@example.com'
            );
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $this->get('/en/auth/google/callback');

            $existingUser->refresh();

            expect($existingUser->google_id)->toBe('new-google-id');
            expect(User::count())->toBe(1);
            $this->assertAuthenticatedAs($existingUser);
        });

        it('marks email as verified when linking to unverified user', function (): void {
            $existingUser = User::factory()->create([
                'email' => 'unverified@example.com',
                'email_verified_at' => null,
            ]);

            $socialiteUser = createMockedSocialiteUser(email: 'unverified@example.com');
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $this->get('/en/auth/google/callback');

            $existingUser->refresh();

            expect($existingUser->email_verified_at)->not->toBeNull();
        });

        it('preserves existing email verification when linking', function (): void {
            $verifiedAt = now()->subDays(5);
            $existingUser = User::factory()->create([
                'email' => 'verified@example.com',
                'email_verified_at' => $verifiedAt,
            ]);

            $socialiteUser = createMockedSocialiteUser(email: 'verified@example.com');
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $this->get('/en/auth/google/callback');

            $existingUser->refresh();

            expect($existingUser->email_verified_at->timestamp)->toBe($verifiedAt->timestamp);
        });

        it('updates avatar when linking social account', function (): void {
            $existingUser = User::factory()->create([
                'email' => 'avatar@example.com',
                'avatar' => null,
            ]);

            $socialiteUser = createMockedSocialiteUser(
                email: 'avatar@example.com',
                avatar: 'https://google.com/new-avatar.jpg'
            );
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $this->get('/en/auth/google/callback');

            $existingUser->refresh();

            expect($existingUser->avatar)->toBe('https://google.com/new-avatar.jpg');
        });

    });

    describe('error handling', function (): void {

        it('redirects to home with error when Socialite throws exception', function (): void {
            Socialite::shouldReceive('driver->user')->andThrow(new Exception('OAuth error'));

            $response = $this->withSession(['social_auth_locale' => 'nl'])
                ->get('/en/auth/google/callback');

            $response->assertRedirect('/nl');
            $response->assertSessionHas('error');
        });

        it('redirects with error when registration is disabled', function (): void {
            config(['app.allow_registration' => false]);

            $socialiteUser = createMockedSocialiteUser(email: 'newuser@example.com');
            Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

            $response = $this->withSession(['social_auth_locale' => 'nl'])
                ->get('/en/auth/google/callback');

            $response->assertRedirect('/nl');
            $response->assertSessionHas('error');
            expect(User::where('email', 'newuser@example.com')->exists())->toBeFalse();
        });

    });

});

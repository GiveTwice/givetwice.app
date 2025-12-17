<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;

describe('CreateDefaultListForNewUser', function () {

    describe('list creation', function () {
        it('creates exactly one list when a user registers', function () {
            $user = User::factory()->create();

            event(new Registered($user));

            expect($user->lists)->toHaveCount(1);
        });

        it('sets is_default to true', function () {
            $user = User::factory()->create();

            event(new Registered($user));

            expect($user->lists->first()->is_default)->toBeTrue();
        });

        it('creates list belonging to the registered user', function () {
            $user = User::factory()->create();

            event(new Registered($user));

            expect($user->lists->first()->user_id)->toBe($user->id);
        });

        it('does not create a list when user is created without Registered event', function () {
            $user = User::factory()->create();

            expect($user->lists)->toHaveCount(0);
        });
    });

    describe('slug generation', function () {
        it('creates slug in {id}-{name} format', function () {
            $user = User::factory()->create();

            event(new Registered($user));

            $list = $user->lists->first();

            expect($list->slug)->toMatch('/^\d+-my-wishlist$/');
            expect($list->slug)->toStartWith($list->id.'-');
        });

        it('creates slug with localized name', function (string $locale, string $expectedSlug) {
            $user = User::factory()->create(['locale_preference' => $locale]);

            event(new Registered($user));

            $list = $user->lists->first();

            expect($list->slug)->toMatch('/^\d+-'.$expectedSlug.'$/');
        })->with([
            'English' => ['en', 'my-wishlist'],
            'Dutch' => ['nl', 'mijn-verlanglijstje'],
            'French' => ['fr', 'ma-liste-de-souhaits'],
        ]);
    });

    describe('localization', function () {
        it('creates the default list name in user locale', function (string $locale, string $expectedName) {
            $user = User::factory()->create(['locale_preference' => $locale]);

            event(new Registered($user));

            expect($user->lists->first()->name)->toBe($expectedName);
        })->with([
            'English' => ['en', 'My wishlist'],
            'Dutch' => ['nl', 'Mijn verlanglijstje'],
            'French' => ['fr', 'Ma liste de souhaits'],
        ]);

        it('restores original locale after creating list', function () {
            app()->setLocale('en');
            $user = User::factory()->create(['locale_preference' => 'nl']);

            event(new Registered($user));

            expect(app()->getLocale())->toBe('en');
        });
    });

});

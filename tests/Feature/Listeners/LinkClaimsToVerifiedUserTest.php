<?php

use App\Models\Claim;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Queue;

describe('LinkClaimsToVerifiedUser', function () {

    beforeEach(function () {
        Queue::fake();
    });

    it('links anonymous claims to user when email is verified', function () {
        $email = 'jane@example.com';

        $gift = Gift::factory()->create();
        $claim = Claim::factory()->anonymous()->confirmed()->create([
            'gift_id' => $gift->id,
            'claimer_email' => $email,
        ]);

        $user = User::factory()->create(['email' => $email]);

        event(new Verified($user));

        expect($claim->fresh()->user_id)->toBe($user->id);
    });

    it('links multiple anonymous claims to user when email is verified', function () {
        $email = 'jane@example.com';

        $claims = Claim::factory()
            ->anonymous()
            ->confirmed()
            ->count(3)
            ->create(['claimer_email' => $email]);

        $user = User::factory()->create(['email' => $email]);

        event(new Verified($user));

        $claims->each(function ($claim) use ($user) {
            expect($claim->fresh()->user_id)->toBe($user->id);
        });
    });

    it('does not link claims with different email addresses', function () {
        $claim = Claim::factory()->anonymous()->confirmed()->create([
            'claimer_email' => 'other@example.com',
        ]);

        $user = User::factory()->create(['email' => 'jane@example.com']);

        event(new Verified($user));

        expect($claim->fresh()->user_id)->toBeNull();
    });

    it('does not overwrite claims already linked to a user', function () {
        $email = 'jane@example.com';
        $existingUser = User::factory()->create();

        $claim = Claim::factory()->create([
            'user_id' => $existingUser->id,
            'claimer_email' => $email,
        ]);

        $newUser = User::factory()->create(['email' => $email]);

        event(new Verified($newUser));

        expect($claim->fresh()->user_id)->toBe($existingUser->id);
    });

    it('links pending anonymous claims to user when email is verified', function () {
        $email = 'jane@example.com';

        $claim = Claim::factory()->anonymous()->pending()->create([
            'claimer_email' => $email,
        ]);

        $user = User::factory()->create(['email' => $email]);

        event(new Verified($user));

        expect($claim->fresh()->user_id)->toBe($user->id);
    });

    it('does not link claims when user is created without Verified event', function () {
        $email = 'jane@example.com';

        $claim = Claim::factory()->anonymous()->confirmed()->create([
            'claimer_email' => $email,
        ]);

        User::factory()->create(['email' => $email]);

        expect($claim->fresh()->user_id)->toBeNull();
    });

    it('links claims when emails match (emails are normalized to lowercase at input)', function () {
        $claim = Claim::factory()->anonymous()->confirmed()->create([
            'claimer_email' => 'jane.doe@example.com',
        ]);

        $user = User::factory()->create(['email' => 'jane.doe@example.com']);

        event(new Verified($user));

        expect($claim->fresh()->user_id)->toBe($user->id);
    });

});

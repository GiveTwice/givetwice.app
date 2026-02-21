<?php

use App\Models\User;

describe('Track last activity', function () {
    it('updates last_active_at on authenticated request', function () {
        $user = User::factory()->create(['last_active_at' => null]);

        $this->actingAs($user)->get('/en/dashboard');

        $user->refresh();
        expect($user->last_active_at)->not->toBeNull();
    });

    it('does not update last_active_at if updated less than 1 hour ago', function () {
        $recentTime = now()->subMinutes(30);
        $user = User::factory()->create(['last_active_at' => $recentTime]);

        $this->actingAs($user)->get('/en/dashboard');

        $user->refresh();
        expect($user->last_active_at->toDateTimeString())->toBe($recentTime->toDateTimeString());
    });

    it('updates last_active_at if older than 1 hour', function () {
        $oldTime = now()->subHours(2);
        $user = User::factory()->create(['last_active_at' => $oldTime]);

        $this->actingAs($user)->get('/en/dashboard');

        $user->refresh();
        expect($user->last_active_at->gt($oldTime))->toBeTrue();
    });

    it('does not update last_active_at for guest requests', function () {
        $this->get('/en');

        expect(User::count())->toBe(0);
    });

    it('uses updateQuietly so updated_at is not changed by the middleware', function () {
        $user = User::factory()->create(['last_active_at' => null]);

        $this->actingAs($user)->get('/en/dashboard');

        $user->refresh();
        expect($user->last_active_at)->not->toBeNull();
        expect($user->updated_at->toDateTimeString())->toBe($user->created_at->toDateTimeString());
    });
});

describe('Migration backfill', function () {
    it('casts last_active_at and inactive_warning_sent_at as datetime', function () {
        $user = User::factory()->create([
            'last_active_at' => now(),
            'inactive_warning_sent_at' => now(),
        ]);

        $user->refresh();
        expect($user->last_active_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($user->inactive_warning_sent_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    it('allows nullable last_active_at and inactive_warning_sent_at', function () {
        $user = User::factory()->create([
            'last_active_at' => null,
            'inactive_warning_sent_at' => null,
        ]);

        $user->refresh();
        expect($user->last_active_at)->toBeNull();
        expect($user->inactive_warning_sent_at)->toBeNull();
    });
});

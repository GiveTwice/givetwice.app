<?php

use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Spatie\SlackAlerts\Facades\SlackAlert;

beforeEach(function () {
    Queue::fake();
    SlackAlert::fake();
});

describe('Delete inactive accounts', function () {
    it('deletes users inactive for 24+ months who were warned 2+ months ago', function () {
        $user = User::factory()->create([
            'last_active_at' => now()->subMonths(25),
            'inactive_warning_sent_at' => now()->subMonths(3),
            'is_admin' => false,
        ]);

        $this->artisan('app:delete-inactive-accounts')
            ->expectsOutputToContain('Deleted 1 inactive account(s)')
            ->assertSuccessful();

        expect(User::find($user->id))->toBeNull();
    });

    it('does not delete users active within 24 months', function () {
        $user = User::factory()->create([
            'last_active_at' => now()->subMonths(23),
            'inactive_warning_sent_at' => now()->subMonths(3),
            'is_admin' => false,
        ]);

        $this->artisan('app:delete-inactive-accounts')->assertSuccessful();

        expect(User::find($user->id))->not->toBeNull();
    });

    it('does not delete users without a warning sent', function () {
        $user = User::factory()->create([
            'last_active_at' => now()->subMonths(25),
            'inactive_warning_sent_at' => null,
            'is_admin' => false,
        ]);

        $this->artisan('app:delete-inactive-accounts')->assertSuccessful();

        expect(User::find($user->id))->not->toBeNull();
    });

    it('does not delete users warned less than 2 months ago', function () {
        $user = User::factory()->create([
            'last_active_at' => now()->subMonths(25),
            'inactive_warning_sent_at' => now()->subMonth(),
            'is_admin' => false,
        ]);

        $this->artisan('app:delete-inactive-accounts')->assertSuccessful();

        expect(User::find($user->id))->not->toBeNull();
    });

    it('does not delete admin users', function () {
        $user = User::factory()->create([
            'last_active_at' => now()->subMonths(25),
            'inactive_warning_sent_at' => now()->subMonths(3),
            'is_admin' => true,
        ]);

        $this->artisan('app:delete-inactive-accounts')->assertSuccessful();

        expect(User::find($user->id))->not->toBeNull();
    });

    it('lists accounts in dry-run mode without deleting', function () {
        $user = User::factory()->create([
            'last_active_at' => now()->subMonths(25),
            'inactive_warning_sent_at' => now()->subMonths(3),
            'is_admin' => false,
        ]);

        $this->artisan('app:delete-inactive-accounts', ['--dry-run' => true])
            ->expectsOutputToContain('Dry run: found 1 account(s) eligible for deletion')
            ->expectsOutputToContain($user->email)
            ->assertSuccessful();

        expect(User::find($user->id))->not->toBeNull();
    });

    it('does not delete users with null last_active_at and recent created_at', function () {
        $user = User::factory()->create([
            'last_active_at' => null,
            'created_at' => now()->subMonths(1),
            'inactive_warning_sent_at' => now()->subMonths(3),
            'is_admin' => false,
        ]);

        $this->artisan('app:delete-inactive-accounts')->assertSuccessful();

        expect(User::find($user->id))->not->toBeNull();
    });

    it('deletes users with null last_active_at and old created_at', function () {
        $user = User::factory()->create([
            'last_active_at' => null,
            'created_at' => now()->subMonths(25),
            'inactive_warning_sent_at' => now()->subMonths(3),
            'is_admin' => false,
        ]);

        $this->artisan('app:delete-inactive-accounts')
            ->expectsOutputToContain('Deleted 1 inactive account(s)')
            ->assertSuccessful();

        expect(User::find($user->id))->toBeNull();
    });

    it('outputs the count of deleted accounts', function () {
        User::factory()->create([
            'last_active_at' => now()->subMonths(25),
            'inactive_warning_sent_at' => now()->subMonths(3),
            'is_admin' => false,
        ]);

        User::factory()->create([
            'last_active_at' => now()->subMonths(30),
            'inactive_warning_sent_at' => now()->subMonths(6),
            'is_admin' => false,
        ]);

        $this->artisan('app:delete-inactive-accounts')
            ->expectsOutputToContain('Deleted 2 inactive account(s)')
            ->assertSuccessful();
    });
});

<?php

use App\Models\ListInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

describe('Prune expired data', function () {
    it('prunes accepted invitations older than 60 days', function () {
        ListInvitation::factory()->accepted()->create([
            'created_at' => now()->subDays(61),
        ]);

        ListInvitation::factory()->accepted()->create([
            'created_at' => now()->subDays(30),
        ]);

        $this->artisan('app:prune-expired-data')->assertSuccessful();

        expect(ListInvitation::count())->toBe(1);
    });

    it('prunes declined invitations older than 60 days', function () {
        ListInvitation::factory()->declined()->create([
            'created_at' => now()->subDays(61),
        ]);

        ListInvitation::factory()->declined()->create([
            'created_at' => now()->subDays(30),
        ]);

        $this->artisan('app:prune-expired-data')->assertSuccessful();

        expect(ListInvitation::count())->toBe(1);
    });

    it('prunes expired invitations older than 60 days', function () {
        ListInvitation::factory()->expired()->create([
            'created_at' => now()->subDays(61),
        ]);

        ListInvitation::factory()->expired()->create([
            'created_at' => now()->subDays(30),
        ]);

        $this->artisan('app:prune-expired-data')->assertSuccessful();

        expect(ListInvitation::count())->toBe(1);
    });

    it('does not prune pending invitations', function () {
        ListInvitation::factory()->create([
            'created_at' => now()->subDays(90),
            'expires_at' => now()->addDays(5),
        ]);

        $this->artisan('app:prune-expired-data')->assertSuccessful();

        expect(ListInvitation::count())->toBe(1);
    });

    it('prunes password reset tokens older than 1 hour', function () {
        DB::table('password_reset_tokens')->insert([
            'email' => 'old@example.com',
            'token' => Str::random(64),
            'created_at' => now()->subHours(2),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => 'fresh@example.com',
            'token' => Str::random(64),
            'created_at' => now()->subMinutes(30),
        ]);

        $this->artisan('app:prune-expired-data')->assertSuccessful();

        expect(DB::table('password_reset_tokens')->count())->toBe(1);
        expect(DB::table('password_reset_tokens')->where('email', 'fresh@example.com')->exists())->toBeTrue();
    });

    it('prunes guest sessions older than 7 days', function () {
        DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => '',
            'last_activity' => now()->subDays(8)->timestamp,
        ]);

        DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => '',
            'last_activity' => now()->subDays(3)->timestamp,
        ]);

        $this->artisan('app:prune-expired-data')->assertSuccessful();

        expect(DB::table('sessions')->whereNull('user_id')->count())->toBe(1);
    });

    it('prunes authenticated sessions older than 30 days', function () {
        $userId = \App\Models\User::factory()->create()->id;

        DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => $userId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => '',
            'last_activity' => now()->subDays(31)->timestamp,
        ]);

        DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => $userId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => '',
            'last_activity' => now()->subDays(10)->timestamp,
        ]);

        $this->artisan('app:prune-expired-data')->assertSuccessful();

        expect(DB::table('sessions')->where('user_id', $userId)->count())->toBe(1);
    });

    it('outputs count for each deletion type', function () {
        ListInvitation::factory()->accepted()->create([
            'created_at' => now()->subDays(90),
        ]);

        $this->artisan('app:prune-expired-data')
            ->expectsOutputToContain('Pruned 1 expired list invitations')
            ->expectsOutputToContain('Pruned 0 expired password reset tokens')
            ->expectsOutputToContain('Pruned 0 stale guest sessions')
            ->expectsOutputToContain('Pruned 0 old authenticated sessions')
            ->assertSuccessful();
    });
});

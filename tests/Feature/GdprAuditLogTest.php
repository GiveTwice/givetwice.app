<?php

use App\Models\GdprAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Spatie\SlackAlerts\Facades\SlackAlert;

describe('GDPR audit log', function () {
    it('logs a data export when user exports their data', function () {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/en/settings/data-export');

        expect(GdprAuditLog::where('action', 'data_export')->count())->toBe(1);

        $log = GdprAuditLog::where('action', 'data_export')->first();
        expect($log->user_id)->toBe($user->id);
        expect($log->user_email)->toBe($user->email);
        expect($log->details)->toBeNull();
        expect($log->performed_by)->toBeNull();
    });

    it('logs an account deletion when user deletes their account', function () {
        Queue::fake();
        SlackAlert::fake();

        $user = User::factory()->create([
            'password' => bcrypt('localdevelopment'),
        ]);
        $email = $user->email;

        $this->actingAs($user)
            ->delete('/en/settings/account', [
                'password' => 'localdevelopment',
            ]);

        expect(GdprAuditLog::where('action', 'account_deletion')->count())->toBe(1);

        $log = GdprAuditLog::where('action', 'account_deletion')->first();
        expect($log->user_id)->toBeNull(); // nullOnDelete after user is deleted
        expect($log->user_email)->toBe($email);
        expect($log->details)->toBeNull();
        expect($log->performed_by)->toBeNull();
    });

    it('logs inactive account deletion with system details', function () {
        Queue::fake();
        SlackAlert::fake();

        $user = User::factory()->create([
            'last_active_at' => now()->subMonths(25),
            'inactive_warning_sent_at' => now()->subMonths(3),
            'is_admin' => false,
        ]);
        $email = $user->email;

        $this->artisan('app:delete-inactive-accounts')->assertSuccessful();

        expect(GdprAuditLog::where('action', 'account_deletion')->count())->toBe(1);

        $log = GdprAuditLog::where('action', 'account_deletion')->first();
        expect($log->user_id)->toBeNull(); // nullOnDelete after user is deleted
        expect($log->user_email)->toBe($email);
        expect($log->details)->toBe('Inactive 24+ months');
        expect($log->performed_by)->toBe('system');
    });

    it('creates audit log entries via the static log helper', function () {
        $user = User::factory()->create();

        GdprAuditLog::log('test_action', $user, 'test details', 'test_actor');

        $log = GdprAuditLog::first();
        expect($log->user_id)->toBe($user->id);
        expect($log->user_email)->toBe($user->email);
        expect($log->action)->toBe('test_action');
        expect($log->details)->toBe('test details');
        expect($log->performed_by)->toBe('test_actor');
        expect($log->created_at)->not->toBeNull();
    });

    it('preserves user_email even after user is deleted', function () {
        Queue::fake();
        SlackAlert::fake();

        $user = User::factory()->create();
        $email = $user->email;

        GdprAuditLog::log('account_deletion', $user);
        $user->delete();

        $log = GdprAuditLog::first();
        expect($log->user_id)->toBeNull();
        expect($log->user_email)->toBe($email);
    });
});

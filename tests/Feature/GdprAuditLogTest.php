<?php

use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;
use Spatie\SlackAlerts\Facades\SlackAlert;

describe('GDPR audit log', function () {
    it('logs a data export when user exports their data', function () {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/en/settings/data-export');

        $log = Activity::where('log_name', 'gdpr')->where('event', 'data_export')->sole();
        expect($log->subject_id)->toBe($user->id)
            ->and($log->causer_id)->toBe($user->id)
            ->and($log->properties['user_email'])->toBe($user->email);
    });

    it('logs an account deletion when user deletes their account', function () {
        Queue::fake();
        SlackAlert::fake();

        $user = User::factory()->create([
            'password' => bcrypt('localdevelopment'),
        ]);
        $userId = $user->id;
        $email = $user->email;

        $this->actingAs($user)
            ->delete('/en/settings/account', [
                'password' => 'localdevelopment',
            ]);

        $log = Activity::where('log_name', 'gdpr')->where('event', 'account_deletion')->sole();
        expect($log->subject_id)->toBe($userId)
            ->and($log->causer_id)->toBe($userId)
            ->and($log->properties['user_email'])->toBe($email)
            ->and($log->properties['user_id'])->toBe($userId)
            ->and($log->properties['details'])->toBe('User-initiated account deletion')
            ->and($log->properties['performed_by'])->toBe('user');
    });

    it('logs inactive account deletion with system details', function () {
        Queue::fake();
        SlackAlert::fake();

        $user = User::factory()->create([
            'last_active_at' => now()->subMonths(25),
            'inactive_warning_sent_at' => now()->subMonths(3),
            'is_admin' => false,
        ]);
        $userId = $user->id;
        $email = $user->email;

        $this->artisan('app:delete-inactive-accounts')->assertSuccessful();

        $log = Activity::where('log_name', 'gdpr')->where('event', 'account_deletion')->sole();
        expect($log->subject_id)->toBe($userId)
            ->and($log->causer_id)->toBeNull()
            ->and($log->properties['user_email'])->toBe($email)
            ->and($log->properties['user_id'])->toBe($userId)
            ->and($log->properties['details'])->toBe('Inactive 24+ months')
            ->and($log->properties['performed_by'])->toBe('system');
    });

    it('preserves user_email even after user is deleted', function () {
        Queue::fake();
        SlackAlert::fake();

        $user = User::factory()->create();
        $userId = $user->id;
        $email = $user->email;

        app(App\Actions\DeleteAccountAction::class)->execute($user, 'Test deletion');

        expect(User::find($userId))->toBeNull();

        $log = Activity::where('log_name', 'gdpr')->where('event', 'account_deletion')->sole();
        expect($log->subject_id)->toBe($userId)
            ->and($log->properties['user_email'])->toBe($email);
    });
});

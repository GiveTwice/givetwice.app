<?php

use App\Mail\InactiveAccountWarningMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

describe('Warn inactive accounts', function () {
    it('sends warning to users inactive for 22+ months', function () {
        Mail::fake();

        $user = User::factory()->create([
            'last_active_at' => now()->subMonths(23),
            'inactive_warning_sent_at' => null,
            'is_admin' => false,
        ]);

        $this->artisan('app:warn-inactive-accounts')->assertSuccessful();

        Mail::assertSent(InactiveAccountWarningMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    });

    it('does not warn users active within 22 months', function () {
        Mail::fake();

        User::factory()->create([
            'last_active_at' => now()->subMonths(21),
            'inactive_warning_sent_at' => null,
            'is_admin' => false,
        ]);

        $this->artisan('app:warn-inactive-accounts')->assertSuccessful();

        Mail::assertNothingSent();
    });

    it('does not warn users who already received a warning', function () {
        Mail::fake();

        User::factory()->create([
            'last_active_at' => now()->subMonths(23),
            'inactive_warning_sent_at' => now()->subWeeks(2),
            'is_admin' => false,
        ]);

        $this->artisan('app:warn-inactive-accounts')->assertSuccessful();

        Mail::assertNothingSent();
    });

    it('does not warn admin users', function () {
        Mail::fake();

        User::factory()->create([
            'last_active_at' => now()->subMonths(23),
            'inactive_warning_sent_at' => null,
            'is_admin' => true,
        ]);

        $this->artisan('app:warn-inactive-accounts')->assertSuccessful();

        Mail::assertNothingSent();
    });

    it('sets inactive_warning_sent_at after sending', function () {
        Mail::fake();

        $user = User::factory()->create([
            'last_active_at' => now()->subMonths(23),
            'inactive_warning_sent_at' => null,
            'is_admin' => false,
        ]);

        $this->artisan('app:warn-inactive-accounts')->assertSuccessful();

        $user->refresh();
        expect($user->inactive_warning_sent_at)->not->toBeNull();
    });

    it('outputs the count of warnings sent', function () {
        Mail::fake();

        User::factory()->create([
            'last_active_at' => now()->subMonths(23),
            'inactive_warning_sent_at' => null,
            'is_admin' => false,
        ]);

        User::factory()->create([
            'last_active_at' => now()->subMonths(25),
            'inactive_warning_sent_at' => null,
            'is_admin' => false,
        ]);

        $this->artisan('app:warn-inactive-accounts')
            ->expectsOutputToContain('Sent 2 inactive account warning(s)')
            ->assertSuccessful();
    });

    it('renders the email with login URL and data export mention', function () {
        $user = User::factory()->create([
            'locale_preference' => 'en',
        ]);

        $mail = new InactiveAccountWarningMail($user);

        $rendered = $mail->render();

        expect($rendered)
            ->toContain('/en/login')
            ->toContain('/en/settings');
    });
});

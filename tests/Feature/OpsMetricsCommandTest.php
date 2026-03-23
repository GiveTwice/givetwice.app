<?php

use App\Events\GiftCreated;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;

function runOpsMetrics(): array
{
    Artisan::call('ops:metrics');
    $output = Artisan::output();

    return json_decode(trim($output), true);
}

describe('Ops metrics command', function () {
    beforeEach(function () {
        Event::fake([GiftCreated::class]);
    });

    it('outputs valid JSON and exits successfully', function () {
        $metrics = runOpsMetrics();

        expect($metrics)->toBeArray();
        expect($metrics)->toHaveKeys([
            'users_total', 'signups_today', 'signups_week', 'signups_month',
            'active_7d', 'lists_total', 'gifts_total', 'claims_total',
            'failed_jobs', 'pending_jobs',
        ]);
    });

    it('counts users correctly', function () {
        User::factory()->count(3)->create();

        $metrics = runOpsMetrics();

        expect($metrics['users_total'])->toBe(3);
    });

    it('counts only today signups for signups_today', function () {
        User::factory()->create(['created_at' => now()]);
        User::factory()->create(['created_at' => now()->subDays(2)]);

        $metrics = runOpsMetrics();

        expect($metrics['signups_today'])->toBe(1);
    });

    it('counts only recent users for active_7d', function () {
        User::factory()->create(['last_active_at' => now()->subDays(3)]);
        User::factory()->create(['last_active_at' => now()->subDays(10)]);
        User::factory()->create(['last_active_at' => null]);

        $metrics = runOpsMetrics();

        expect($metrics['active_7d'])->toBe(1);
    });

    it('includes content metrics', function () {
        $user = User::factory()->create();
        GiftList::factory()->for($user, 'creator')->create();
        $gifts = Gift::factory()->for($user)->count(2)->create();
        Claim::factory()->for($gifts->first())->count(3)->create();

        $metrics = runOpsMetrics();

        expect($metrics['lists_total'])->toBe(1);
        expect($metrics['gifts_total'])->toBe(2);
        expect($metrics['claims_total'])->toBe(3);
    });

    it('includes health metrics', function () {
        $metrics = runOpsMetrics();

        expect($metrics['failed_jobs'])->toBe(0);
        expect($metrics['pending_jobs'])->toBe(0);
    });

    it('works on an empty database', function () {
        $metrics = runOpsMetrics();

        expect($metrics['users_total'])->toBe(0);
        expect($metrics['signups_today'])->toBe(0);
        expect($metrics['active_7d'])->toBe(0);
        expect($metrics['lists_total'])->toBe(0);
        expect($metrics['gifts_total'])->toBe(0);
        expect($metrics['claims_total'])->toBe(0);
        expect($metrics['failed_jobs'])->toBe(0);
        expect($metrics['pending_jobs'])->toBe(0);
    });
});

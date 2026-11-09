<?php

namespace App\Console\Commands;

use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

#[Signature('ops:metrics')]
#[Description('Output production metrics as JSON for the Monitor Agent')]
class OpsMetricsCommand extends Command
{
    public function handle(): int
    {
        $metrics = [
            'users_total' => User::count(),
            'signups_today' => User::whereDate('created_at', today())->count(),
            'signups_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'signups_month' => User::where('created_at', '>=', now()->subMonth())->count(),
            'active_7d' => User::where('last_active_at', '>=', now()->subDays(7))->count(),
            'lists_total' => GiftList::count(),
            'gifts_total' => Gift::count(),
            'claims_total' => Claim::count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'pending_jobs' => Queue::size('default') + Queue::size('fetch'),
        ];

        $this->line(json_encode($metrics, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}

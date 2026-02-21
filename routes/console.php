<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Run health checks every minute (results stored for Oh Dear polling)
Schedule::command('health:check')
    ->everyMinute()
    ->monitorName('Health Check');

// Capture Horizon queue metrics for the dashboard
Schedule::command('horizon:snapshot')
    ->everyFiveMinutes()
    ->monitorName('Horizon Snapshot');

// Prune old health check results (keeps 5 days by default)
Schedule::command('model:prune', ['--model' => HealthCheckResultHistoryItem::class])
    ->daily()
    ->monitorName('Prune Health Check History');

// Prune old schedule monitor logs (keeps 30 days by default)
Schedule::command('model:prune', ['--model' => MonitoredScheduledTaskLogItem::class])
    ->daily()
    ->monitorName('Prune Schedule Monitor Logs');

// Regenerate sitemap weekly (also generated on deploy)
Schedule::command('sitemap:generate')
    ->weekly()
    ->monitorName('Generate Sitemap');

// Send friend wishlist digest emails daily at 1PM UTC
Schedule::command('friends:send-digest')
    ->dailyAt('13:00')
    ->monitorName('Friend Digest Emails');

// Prune expired invitations, password tokens, and stale sessions
Schedule::command('app:prune-expired-data')
    ->daily()
    ->monitorName('Prune Expired Data');

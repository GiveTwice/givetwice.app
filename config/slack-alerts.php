<?php

return [
    'enabled' => env('SLACK_ALERT_ENABLED', true),

    'webhook_urls' => [
        'default' => env('SLACK_ALERT_WEBHOOK'),
    ],

    'job' => Spatie\SlackAlerts\Jobs\SendToSlackChannelJob::class,
    'queue' => env('SLACK_ALERT_QUEUE', 'default'),
];

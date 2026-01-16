<?php

return [
    'enabled' => env('CONTROL_PLANE_ENABLED', false),
    'base_url' => env('CONTROL_PLANE_URL', ''),
    'token' => env('CONTROL_PLANE_TOKEN'),
    'registration_token' => env('CONTROL_PLANE_REGISTRATION_TOKEN'),
    'timeout' => (int) env('CONTROL_PLANE_TIMEOUT', 5),
    'retry' => (int) env('CONTROL_PLANE_RETRY', 2),
    'schedule' => [
        'enabled' => env('CONTROL_PLANE_SCHEDULE_ENABLED', true),
        'heartbeat_cron' => env('CONTROL_PLANE_HEARTBEAT_CRON', '* * * * *'),
        'license_refresh_cron' => env('CONTROL_PLANE_LICENSE_REFRESH_CRON', '0 * * * *'),
    ],
];

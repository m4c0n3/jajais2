<?php

return [
    'enabled' => env('CONTROL_PLANE_ENABLED', false),
    'base_url' => env('CONTROL_PLANE_URL', ''),
    'token' => env('CONTROL_PLANE_TOKEN'),
    'registration_token' => env('CONTROL_PLANE_REGISTRATION_TOKEN'),
    'timeout' => (int) env('CONTROL_PLANE_TIMEOUT', 5),
    'retry' => (int) env('CONTROL_PLANE_RETRY', 2),
    'retry_backoff_ms' => (int) env('CONTROL_PLANE_RETRY_BACKOFF_MS', 200),
    'retry_max_seconds' => (int) env('CONTROL_PLANE_RETRY_MAX_SECONDS', 10),
    'jwt_public_key' => env('CONTROL_PLANE_JWT_PUBLIC_KEY'),
    'jwt_public_key_path' => env('CONTROL_PLANE_JWT_PUBLIC_KEY_PATH'),
    'jwt_issuer' => env('CONTROL_PLANE_JWT_ISSUER'),
    'jwt_audience' => env('CONTROL_PLANE_JWT_AUDIENCE'),
    'schedule' => [
        'enabled' => env('CONTROL_PLANE_SCHEDULE_ENABLED', true),
        'heartbeat_cron' => env('CONTROL_PLANE_HEARTBEAT_CRON', '* * * * *'),
        'license_refresh_cron' => env('CONTROL_PLANE_LICENSE_REFRESH_CRON', '0 * * * *'),
    ],
];

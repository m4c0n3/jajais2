<?php

return [
    'channel' => env('UPDATES_CHANNEL', 'stable'),

    'manifest_url' => env('UPDATES_MANIFEST_URL'),
    'manifest_path' => env('UPDATES_MANIFEST_PATH'),

    'jwt_public_key' => env('UPDATES_JWT_PUBLIC_KEY'),
    'jwt_public_key_path' => env('UPDATES_JWT_PUBLIC_KEY_PATH'),
    'jwt_issuer' => env('UPDATES_JWT_ISSUER'),
    'jwt_audience' => env('UPDATES_JWT_AUDIENCE'),
];

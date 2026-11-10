<?php

return [
    'mobile' => [
        'api_version' => 1,
        'minimum_app_version' => env('ONEPOS_MINIMUM_MOBILE_VERSION', '1.0.0'),
    ],

    'demo' => [
        'enabled' => (bool) env('ONEPOS_DEMO_GUEST_ENABLED', false),
        'database_configured' => filled(env('DEMO_DB_DATABASE')) || filled(env('DEMO_DB_URL')),
        'email' => env('ONEPOS_DEMO_GUEST_EMAIL', 'demo-guest@pos.onetech.bd'),
        'ticket_ttl_seconds' => 60,
    ],
];

<?php

return [
    'customers_mail_cloud' => [
        'api_key' => env('CMC_API_KEY', ''),
        'api_user' => env('CMC_API_USER', ''),
        'endpoint' => env('CMC_ENDPOINT', ''),
    ],
    'sentry' => [
        'laravel_dsn' => env('SENTRY_LARAVEL_DSN', ''),
        'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', '')
    ]
];

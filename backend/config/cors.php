<?php

$configuredOrigins = array_filter([
    ...explode(',', (string) env(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:5173,http://127.0.0.1:5173',
    )),
    env('FRONTEND_URL'),
]);

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_unique(array_map(
        static fn (string $origin): string => rtrim(trim($origin), '/'),
        $configuredOrigins,
    ))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];

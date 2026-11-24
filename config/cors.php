<?php

declare(strict_types=1);

/**
 * The marketing theme (Vue/Vite) is usually served from another origin than this API.
 * Set CORS_ALLOWED_ORIGINS="https://www.example.com,http://localhost:5173" in .env.
 * Defaults to "*" (fine for the public, read-only search endpoints; tokens are Bearer, not cookies).
 */
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*')))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];

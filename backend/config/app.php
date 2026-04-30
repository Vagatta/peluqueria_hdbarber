<?php

return [
    'name' => env('APP_NAME', 'HDBarber'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'Europe/Madrid'),
    'timezone_display' => env('APP_TIMEZONE_DISPLAY', 'Europe/Madrid'),
    'locale' => 'es',
    'fallback_locale' => 'en',
    'faker_locale' => 'es_ES',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [],
    'maintenance' => [
        'driver' => 'file',
    ],
];

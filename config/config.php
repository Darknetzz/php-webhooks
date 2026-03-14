<?php

declare(strict_types=1);

// Prefer $_ENV (from .env) so config works when putenv() is disabled (e.g. on some hosts)
$env = static fn (string $key, string $default = '') => $_ENV[$key] ?? (getenv($key) ?: $default);

$basePath = $env('APP_BASE_PATH', '');
$basePath = $basePath !== '' ? '/' . trim($basePath, '/') : '';

return [
    'env' => $env('APP_ENV', 'production'),
    'debug' => filter_var($env('APP_DEBUG', '0'), FILTER_VALIDATE_BOOL),
    'base_path' => $basePath,
    'url' => rtrim($env('APP_URL', 'http://localhost'), '/'),
    /** Public URL for webhook endpoints (e.g. https://webhooks.example.com). If set, webhook URLs in the UI use this; otherwise APP_URL is used. */
    'url_public' => $env('APP_URL_PUBLIC', '') !== '' ? rtrim($env('APP_URL_PUBLIC', ''), '/') : null,
    'secret' => $env('APP_SECRET', '') ?: null,

    'database' => [
        'driver' => $env('DB_DRIVER', 'sqlite'),
        'sqlite' => [
            'path' => $env('DB_PATH', '') ?: __DIR__ . '/../data/database.sqlite',
        ],
        'mysql' => [
            'host' => $env('DB_HOST', '127.0.0.1'),
            'port' => (int) $env('DB_PORT', '3306'),
            'dbname' => $env('DB_NAME', 'webhooks'),
            'user' => $env('DB_USER', ''),
            'password' => $env('DB_PASSWORD', ''),
            'charset' => $env('DB_CHARSET', 'utf8mb4'),
        ],
    ],

    'session' => [
        'name' => 'php_webhooks_sid',
        'lifetime' => 86400 * 7, // 7 days
    ],
];

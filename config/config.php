<?php

declare(strict_types=1);

return [
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOL),
    'url' => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),
    'secret' => getenv('APP_SECRET') ?: null,

    'database' => [
        'driver' => getenv('DB_DRIVER') ?: 'sqlite',
        'sqlite' => [
            'path' => getenv('DB_PATH') ?: __DIR__ . '/../data/database.sqlite',
        ],
        'mysql' => [
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => (int) (getenv('DB_PORT') ?: 3306),
            'dbname' => getenv('DB_NAME') ?: 'webhooks',
            'user' => getenv('DB_USER') ?: '',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        ],
    ],

    'session' => [
        'name' => 'php_webhooks_sid',
        'lifetime' => 86400 * 7, // 7 days
    ],
];

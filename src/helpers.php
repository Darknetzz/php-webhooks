<?php

declare(strict_types=1);

if (!function_exists('config')) {
    function config(): array
    {
        static $config = null;
        if ($config === null) {
            $config = require dirname(__DIR__) . '/config/config.php';
        }
        return $config;
    }
}

if (!function_exists('db')) {
    function db(): \App\Database
    {
        static $db = null;
        if ($db === null) {
            $db = new \App\Database(config()['database']);
        }
        return $db;
    }
}

if (!function_exists('auth')) {
    function auth(): \App\Auth
    {
        static $auth = null;
        if ($auth === null) {
            $auth = new \App\Auth();
        }
        return $auth;
    }
}

if (!function_exists('e')) {
    function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $code = 302): never
    {
        header('Location: ' . $url, true, $code);
        exit;
    }
}

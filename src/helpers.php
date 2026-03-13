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

/** Base URL for links/redirects: uses APP_URL when request host matches, else derived from request (for direct access e.g. web01/webhooks/public). */
if (!function_exists('base_url')) {
    function base_url(): string
    {
        $config = config();
        $configured = rtrim((string) ($config['url'] ?? ''), '/');
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';
        $host = is_string($host) ? trim(explode(',', $host)[0]) : '';
        $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ($_SERVER['REQUEST_SCHEME'] ?? 'http');
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $requestBase = $scheme . '://' . $host . ($scriptDir !== '' && $scriptDir !== '/' ? $scriptDir : '');
        if ($configured !== '' && $host !== '') {
            $configuredHost = parse_url($configured, PHP_URL_HOST);
            $requestHost = parse_url('http://' . $host, PHP_URL_HOST) ?: $host;
            if ($configuredHost !== null && $configuredHost !== false && strcasecmp($configuredHost, $requestHost) === 0) {
                return $configured;
            }
        }
        return $requestBase;
    }
}

/** Base URL for webhook endpoints (e.g. in UI and curl examples). Use APP_URL_PUBLIC when set, otherwise base_url(). */
if (!function_exists('webhook_base_url')) {
    function webhook_base_url(): string
    {
        $config = config();
        $public = $config['url_public'] ?? null;
        return $public !== null && $public !== '' ? $public : base_url();
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $code = 302): never
    {
        header('Location: ' . $url, true, $code);
        exit;
    }
}

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

/**
 * Base path prefix for the app (e.g. '' or '/webhooks/public').
 * Used for routing (strip from REQUEST_URI) and for link generation.
 * Priority: X-Forwarded-Prefix (reverse proxy) → path from APP_URL → SCRIPT_NAME directory.
 */
if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $prefix = $_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? null;
        if ($prefix !== null && $prefix !== '') {
            $prefix = '/' . trim((string) $prefix, '/');
            return $prefix === '/' ? '' : $prefix;
        }
        $configured = config()['url'] ?? '';
        if ($configured !== '') {
            $path = parse_url($configured, PHP_URL_PATH);
            if ($path !== null && $path !== '' && $path !== '/') {
                return rtrim($path, '/');
            }
        }
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        return ($scriptDir !== '' && $scriptDir !== '/') ? $scriptDir : '';
    }
}

/**
 * Request path for routing (path only, no query string). Base path is stripped.
 * Example: REQUEST_URI /webhooks/public/login → /login
 */
if (!function_exists('request_path')) {
    function request_path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = $uri === null || $uri === false ? '/' : $uri;
        $base = app_base_path();
        if ($base !== '' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base)) ?: '/';
        }
        return '/' . trim((string) $uri, '/');
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
        $basePath = app_base_path();
        $requestBase = $scheme . '://' . $host . $basePath;
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

if (!function_exists('redirect')) {
    function redirect(string $url, int $code = 302): never
    {
        header('Location: ' . $url, true, $code);
        exit;
    }
}

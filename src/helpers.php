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

/** Base URL for links/redirects: uses APP_URL when request host matches, else derived from request (for direct access e.g. <yourserver>/webhooks/public). */
if (!function_exists('base_url')) {
    function base_url(): string
    {
        $config = config();
        $configured = rtrim((string) ($config['url'] ?? ''), '/');
        $basePath = $config['base_path'] ?? '';
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';
        $host = is_string($host) ? trim(explode(',', $host)[0]) : '';
        $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ($_SERVER['REQUEST_SCHEME'] ?? 'http');
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $requestBase = $scheme . '://' . $host . ($basePath !== '' ? $basePath : ($scriptDir !== '' && $scriptDir !== '/' ? $scriptDir : ''));
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

/** Project root (parent of src/). */
if (!function_exists('project_root')) {
    function project_root(): string
    {
        return dirname(__DIR__);
    }
}

/**
 * Current Git version: commit (short hash) and optional tag.
 * Uses version.txt when present (Docker/build), else runs git from project root.
 * @return array{commit: string, tag: string|null}|null
 */
if (!function_exists('git_version')) {
    function git_version(): ?array
    {
        $root = project_root();
        $versionFile = $root . '/version.txt';
        if (is_file($versionFile) && is_readable($versionFile)) {
            $raw = trim((string) file_get_contents($versionFile));
            if ($raw === '') {
                return null;
            }
            $parts = preg_split('/\s+/', $raw, 2);
            if (count($parts) === 1) {
                return ['commit' => $parts[0], 'tag' => null];
            }
            return ['commit' => $parts[1], 'tag' => $parts[0] !== '' ? $parts[0] : null];
        }
        $gitDir = $root . '/.git';
        if (!is_dir($gitDir)) {
            return null;
        }
        $commit = @shell_exec('cd ' . escapeshellarg($root) . ' && git rev-parse --short HEAD 2>/dev/null');
        if ($commit === null || $commit === '') {
            return null;
        }
        $commit = trim($commit);
        $tag = @shell_exec('cd ' . escapeshellarg($root) . ' && git describe --tags --exact-match 2>/dev/null');
        $tag = $tag !== null && $tag !== '' ? trim($tag) : null;
        return ['commit' => $commit, 'tag' => $tag];
    }
}

/**
 * Git repo URL for linking (e.g. to commit). From GIT_REPO_URL env or git remote origin.
 * @return string|null Normalized HTTPS URL without trailing slash, or null
 */
if (!function_exists('git_repo_url')) {
    function git_repo_url(): ?string
    {
        $config = config();
        $url = $config['git_repo_url'] ?? null;
        if ($url !== null && $url !== '') {
            return $url;
        }
        $root = project_root();
        if (!is_dir($root . '/.git')) {
            return null;
        }
        $remote = @shell_exec('cd ' . escapeshellarg($root) . ' && git config --get remote.origin.url 2>/dev/null');
        if ($remote === null || trim($remote) === '') {
            return null;
        }
        $remote = trim($remote);
        // git@github.com:user/repo.git -> https://github.com/user/repo
        if (preg_match('#^git@([^:]+):(.+?)\.git$#', $remote, $m)) {
            return 'https://' . $m[1] . '/' . str_replace(':', '/', $m[2]);
        }
        if (preg_match('#^https?://.+#', $remote)) {
            return rtrim(preg_replace('#\.git$#', '', $remote), '/');
        }
        return null;
    }
}

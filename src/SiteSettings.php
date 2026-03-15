<?php

declare(strict_types=1);

namespace App;

/**
 * Key-value site settings (admin-configurable). Stored in site_settings table.
 */
class SiteSettings
{
    public const KEY_WEBHOOK_TESTING_ENABLED = 'webhook_testing_enabled';
    public const KEY_ALLOW_SPECIFY_TEST_URL = 'allow_specify_test_url';
    public const KEY_MAX_WEBHOOKS_PER_USER = 'max_webhooks_per_user';
    public const KEY_WEBHOOK_TEST_TIMEOUT_SECONDS = 'webhook_test_timeout_seconds';
    public const KEY_ALLOW_REGISTRATION = 'allow_registration';
    public const KEY_SITE_NAME = 'site_name';

    private static ?array $cache = null;

    /** Get a setting value. Returns string from DB; use filter for bool. */
    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::getAll();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /** Get setting as bool. Treats '1', 'true', 'on', 'yes' as true. */
    public static function getBool(string $key, bool $default = true): bool
    {
        $v = self::get($key, $default ? '1' : '0');
        return in_array(strtolower((string) $v), ['1', 'true', 'on', 'yes'], true);
    }

    /** Get all settings as key => value. */
    public static function getAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $pdo = db()->pdo();
        if (!db()->tableExists('site_settings')) {
            self::$cache = [];
            return self::$cache;
        }
        $stmt = $pdo->query('SELECT name, value FROM site_settings');
        $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_KEY_PAIR) : [];
        self::$cache = is_array($rows) ? $rows : [];
        return self::$cache;
    }

    /** Set one setting. */
    public static function set(string $key, string $value): void
    {
        $pdo = db()->pdo();
        $driver = db()->isSqlite() ? 'sqlite' : 'mysql';
        if ($driver === 'sqlite') {
            $pdo->prepare('INSERT INTO site_settings (name, value) VALUES (?, ?) ON CONFLICT(name) DO UPDATE SET value = excluded.value')
                ->execute([$key, $value]);
        } else {
            $pdo->prepare('INSERT INTO site_settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)')
                ->execute([$key, $value]);
        }
        self::$cache = null;
    }

    /** Set multiple settings from associative array. */
    public static function setAll(array $settings): void
    {
        foreach ($settings as $key => $value) {
            self::set($key, (string) $value);
        }
    }

    /** Clear in-memory cache (e.g. after bulk update). */
    public static function clearCache(): void
    {
        self::$cache = null;
    }
}

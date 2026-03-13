#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Run from project root to see the actual database error:
 *   php check-db.php
 */

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/src/Database.php';

$config = config();
$dbConfig = $config['database'];

echo "Driver: " . ($dbConfig['driver'] ?? '?') . "\n";
if (($dbConfig['driver'] ?? '') === 'sqlite') {
    $path = $dbConfig['sqlite']['path'] ?? __DIR__ . '/data/database.sqlite';
    if ($path !== '' && $path[0] !== '/' && preg_match('#^[A-Za-z]:#', $path) === 0) {
        $path = __DIR__ . '/' . $path;
    }
    $dir = dirname($path);
    echo "SQLite path: " . $path . "\n";
    echo "Dir exists: " . (is_dir($dir) ? 'yes' : 'no') . "\n";
    $writable = is_dir($dir) && is_writable($dir);
    echo "Dir writable (by current user " . get_current_user() . "): " . ($writable ? 'yes' : 'no') . "\n";
    if (!$writable && get_current_user() !== 'www-data') {
        echo "  (To test web server permissions, run: sudo -u www-data php " . basename(__FILE__) . ")\n";
    }
}

echo "\nConnecting...\n";

try {
    $db = new \App\Database($dbConfig);
    $db->migrate();
    echo "OK. Database is ready.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

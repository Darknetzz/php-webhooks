#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Run from project root to see the actual database error:
 *   php check-db.php
 */

require_once __DIR__ . '/config/bootstrap.php';

$config = config();
$dbConfig = $config['database'];

echo "Driver: " . ($dbConfig['driver'] ?? '?') . "\n";
if (($dbConfig['driver'] ?? '') === 'sqlite') {
    $path = $dbConfig['sqlite']['path'] ?? __DIR__ . '/data/database.sqlite';
    if ($path !== '' && $path[0] !== '/' && preg_match('#^[A-Za-z]:#', $path) === 0) {
        $path = __DIR__ . '/' . $path;
    }
    echo "SQLite path: " . $path . "\n";
    echo "Dir exists: " . (is_dir(dirname($path)) ? 'yes' : 'no') . "\n";
    echo "Dir writable: " . (is_writable(dirname($path)) ? 'yes' : 'no') . "\n";
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

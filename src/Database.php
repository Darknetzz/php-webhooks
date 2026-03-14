<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;
    private string $driver;

    public function __construct(array $config)
    {
        $this->driver = $config['driver'] ?? 'sqlite';
        $this->pdo = $this->connect($config);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function connect(array $config): PDO
    {
        if ($this->driver === 'sqlite') {
            $path = $config['sqlite']['path'] ?? __DIR__ . '/../data/database.sqlite';
            // Resolve relative paths against project root so DB_PATH works regardless of CWD
            if ($path !== '' && $path[0] !== '/' && preg_match('#^[A-Za-z]:#', $path) === 0) {
                $path = dirname(__DIR__) . '/' . $path;
            }
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            return new PDO('sqlite:' . $path);
        }

        if ($this->driver === 'mysql') {
            $m = $config['mysql'];
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $m['host'],
                $m['port'],
                $m['dbname'],
                $m['charset'] ?? 'utf8mb4'
            );
            return new PDO($dsn, $m['user'], $m['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        }

        throw new \InvalidArgumentException("Unsupported database driver: {$this->driver}");
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function isSqlite(): bool
    {
        return $this->driver === 'sqlite';
    }

    public function isMySQL(): bool
    {
        return $this->driver === 'mysql';
    }

    /** Auto-increment / last insert id syntax */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /** Run migrations (create tables if not exist, add new columns to webhooks if missing) */
    public function migrate(): void
    {
        $schema = $this->getSchema();
        foreach ($schema as $sql) {
            $this->pdo->exec($sql);
        }
        $this->migrateWebhookResponseColumns();
    }

    /** Add optional response columns to webhooks for existing installations. */
    private function migrateWebhookResponseColumns(): void
    {
        if (!$this->tableExists('webhooks')) {
            return;
        }
        $alters = $this->isSqlite()
            ? [
                'ALTER TABLE webhooks ADD COLUMN response_status_code INTEGER NOT NULL DEFAULT 200',
                'ALTER TABLE webhooks ADD COLUMN response_headers TEXT',
                'ALTER TABLE webhooks ADD COLUMN response_body TEXT',
            ]
            : [
                'ALTER TABLE webhooks ADD COLUMN response_status_code INT NOT NULL DEFAULT 200',
                'ALTER TABLE webhooks ADD COLUMN response_headers TEXT',
                'ALTER TABLE webhooks ADD COLUMN response_body TEXT',
            ];
        foreach ($alters as $sql) {
            try {
                $this->pdo->exec($sql);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'duplicate') === false && strpos($e->getMessage(), 'Duplicate') === false) {
                    throw $e;
                }
            }
        }
    }

    private function getSchema(): array
    {
        if ($this->isSqlite()) {
            return [
                "CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username VARCHAR(255) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    role VARCHAR(32) NOT NULL DEFAULT 'admin',
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL
                )",
                "CREATE TABLE IF NOT EXISTS webhooks (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    slug VARCHAR(64) NOT NULL UNIQUE,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    is_public INTEGER NOT NULL DEFAULT 1,
                    response_status_code INTEGER NOT NULL DEFAULT 200,
                    response_headers TEXT,
                    response_body TEXT,
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )",
                "CREATE TABLE IF NOT EXISTS webhook_requests (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    webhook_id INTEGER NOT NULL,
                    method VARCHAR(16) NOT NULL,
                    headers TEXT,
                    body TEXT,
                    query_string TEXT,
                    ip VARCHAR(45),
                    created_at TEXT NOT NULL,
                    FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE
                )",
            ];
        }

        return [
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(32) NOT NULL DEFAULT 'admin',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS webhooks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                slug VARCHAR(64) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                is_public TINYINT(1) NOT NULL DEFAULT 1,
                response_status_code INT NOT NULL DEFAULT 200,
                response_headers TEXT,
                response_body TEXT,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )",
            "CREATE TABLE IF NOT EXISTS webhook_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                webhook_id INT NOT NULL,
                method VARCHAR(16) NOT NULL,
                headers TEXT,
                body TEXT,
                query_string TEXT,
                ip VARCHAR(45),
                created_at DATETIME NOT NULL,
                FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE
            )",
        ];
    }

    public function tableExists(string $table): bool
    {
        if ($this->isSqlite()) {
            $stmt = $this->pdo->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name=" . $this->pdo->quote($table));
            return $stmt && $stmt->fetch() !== false;
        }
        $stmt = $this->pdo->query("SHOW TABLES LIKE " . $this->pdo->quote($table));
        return $stmt && $stmt->fetch() !== false;
    }
}

<?php

declare(strict_types=1);

namespace App;

class UserRepository
{
    public static function find(int $id): ?User
    {
        $pdo = db()->pdo();
        $stmt = $pdo->prepare('SELECT id, username, role, created_at, updated_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? User::fromRow($row) : null;
    }

    public static function findByUsername(string $username): ?User
    {
        $pdo = db()->pdo();
        $stmt = $pdo->prepare('SELECT id, username, role, created_at, updated_at FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? User::fromRow($row) : null;
    }

    public static function getPasswordHash(int $userId): string
    {
        $pdo = db()->pdo();
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['password_hash'] : '';
    }

    public static function count(): int
    {
        return (int) db()->pdo()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public static function create(string $username, string $password, string $role = User::ROLE_ADMIN): User
    {
        $hash = password_hash($password, \PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');
        $pdo = db()->pdo();
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$username, $hash, $role, $now, $now]);
        $user = self::find((int) db()->lastInsertId());
        if (!$user) {
            throw new \RuntimeException('Failed to load created user');
        }
        return $user;
    }
}

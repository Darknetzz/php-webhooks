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

    /** @return User[] */
    public static function listAll(): array
    {
        $stmt = db()->pdo()->query('SELECT id, username, role, created_at, updated_at FROM users ORDER BY username');
        return array_map([User::class, 'fromRow'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
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

    public static function update(int $id, array $data): void
    {
        $allowed = ['username', 'role'];
        $set = [];
        $params = [];
        foreach ($allowed as $k) {
            if (!array_key_exists($k, $data)) {
                continue;
            }
            $set[] = "$k = ?";
            $params[] = $data[$k];
        }
        if (array_key_exists('password', $data) && (string) $data['password'] !== '') {
            $set[] = 'password_hash = ?';
            $params[] = password_hash((string) $data['password'], \PASSWORD_DEFAULT);
        }
        if (empty($set)) {
            return;
        }
        $set[] = 'updated_at = ?';
        $params[] = date('Y-m-d H:i:s');
        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $set) . ' WHERE id = ?';
        db()->pdo()->prepare($sql)->execute($params);
    }

    /** @return int Number of users with role superadmin */
    public static function countSuperAdmins(): int
    {
        $pdo = db()->pdo();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
        $stmt->execute([User::ROLE_SUPERADMIN]);
        return (int) $stmt->fetchColumn();
    }

    public static function delete(int $id): void
    {
        db()->pdo()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    }
}

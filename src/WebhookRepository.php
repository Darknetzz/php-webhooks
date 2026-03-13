<?php

declare(strict_types=1);

namespace App;

use PDO;

class WebhookRepository
{
    public static function find(int $id): ?Webhook
    {
        $stmt = db()->pdo()->prepare('SELECT * FROM webhooks WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Webhook::fromRow($row) : null;
    }

    public static function findBySlug(string $slug): ?Webhook
    {
        $stmt = db()->pdo()->prepare('SELECT * FROM webhooks WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Webhook::fromRow($row) : null;
    }

    /** @return Webhook[] */
    public static function listForUser(int $userId): array
    {
        $stmt = db()->pdo()->prepare('SELECT * FROM webhooks WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        return array_map([Webhook::class, 'fromRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @return Webhook[] Public webhooks (for listing without auth) */
    public static function listPublic(): array
    {
        $stmt = db()->pdo()->query('SELECT * FROM webhooks WHERE is_public = 1 ORDER BY name');
        return array_map([Webhook::class, 'fromRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function create(int $userId, string $slug, string $name, string $description = '', bool $isPublic = true): Webhook
    {
        $now = date('Y-m-d H:i:s');
        $pdo = db()->pdo();
        $stmt = $pdo->prepare('INSERT INTO webhooks (user_id, slug, name, description, is_public, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $slug, $name, $description, $isPublic ? 1 : 0, $now, $now]);
        $hook = self::find((int) db()->lastInsertId());
        if (!$hook) {
            throw new \RuntimeException('Failed to load created webhook');
        }
        return $hook;
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['name', 'description', 'is_public', 'slug'];
        $set = [];
        $params = [];
        foreach ($allowed as $k) {
            if (!array_key_exists($k, $data)) {
                continue;
            }
            if ($k === 'is_public') {
                $set[] = "is_public = ?";
                $params[] = $data[$k] ? 1 : 0;
            } else {
                $set[] = "$k = ?";
                $params[] = $data[$k];
            }
        }
        if (empty($set)) {
            return;
        }
        $set[] = 'updated_at = ?';
        $params[] = date('Y-m-d H:i:s');
        $params[] = $id;
        $sql = 'UPDATE webhooks SET ' . implode(', ', $set) . ' WHERE id = ?';
        db()->pdo()->prepare($sql)->execute($params);
    }

    public static function delete(int $id): void
    {
        db()->pdo()->prepare('DELETE FROM webhooks WHERE id = ?')->execute([$id]);
    }

    public static function userOwns(int $webhookId, int $userId): bool
    {
        $stmt = db()->pdo()->prepare('SELECT 1 FROM webhooks WHERE id = ? AND user_id = ?');
        $stmt->execute([$webhookId, $userId]);
        return $stmt->fetch() !== false;
    }
}

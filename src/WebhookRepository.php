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

    /**
     * List all webhooks with owner username (for admin panel).
     * @return array<int, array{webhook: Webhook, owner_username: string}>
     */
    public static function listAllWithOwner(): array
    {
        $pdo = db()->pdo();
        $stmt = $pdo->query('
            SELECT w.id, w.user_id, w.slug, w.name, w.description, w.is_public, w.requests_public,
                   w.response_status_code, w.response_headers, w.response_body,
                   w.allowed_methods, w.created_at, w.updated_at,
                   u.username AS owner_username
            FROM webhooks w
            JOIN users u ON u.id = w.user_id
            ORDER BY w.created_at DESC
        ');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $owner = $row['owner_username'];
            unset($row['owner_username']);
            $result[] = ['webhook' => Webhook::fromRow($row), 'owner_username' => $owner];
        }
        return $result;
    }

    public static function create(int $userId, string $slug, string $name, string $description = '', bool $isPublic = false, bool $requestsPublic = false, int $responseStatusCode = 200, string $responseHeaders = '', string $responseBody = '', string $allowedMethods = ''): Webhook
    {
        $now = date('Y-m-d H:i:s');
        $pdo = db()->pdo();
        $stmt = $pdo->prepare('INSERT INTO webhooks (user_id, slug, name, description, is_public, requests_public, response_status_code, response_headers, response_body, allowed_methods, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $slug, $name, $description, $isPublic ? 1 : 0, $requestsPublic ? 1 : 0, $responseStatusCode, $responseHeaders, $responseBody, trim($allowedMethods), $now, $now]);
        $hook = self::find((int) db()->lastInsertId());
        if (!$hook) {
            throw new \RuntimeException('Failed to load created webhook');
        }
        return $hook;
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['name', 'description', 'is_public', 'requests_public', 'slug', 'response_status_code', 'response_headers', 'response_body', 'allowed_methods'];
        $set = [];
        $params = [];
        foreach ($allowed as $k) {
            if (!array_key_exists($k, $data)) {
                continue;
            }
            if ($k === 'is_public' || $k === 'requests_public') {
                $set[] = "$k = ?";
                $params[] = $data[$k] ? 1 : 0;
            } elseif ($k === 'response_status_code') {
                $set[] = "response_status_code = ?";
                $params[] = (int) $data[$k];
            } elseif ($k === 'allowed_methods') {
                $set[] = "allowed_methods = ?";
                $params[] = trim((string) $data[$k]);
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

    /** Generate a random URL-safe slug (hex, 10–30 chars). Retries until unique. */
    public static function generateRandomSlug(int $minLength = 10, int $maxLength = 30): string
    {
        $minLength = max(10, min(30, $minLength));
        $maxLength = max($minLength, min(30, $maxLength));
        $maxAttempts = 20;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $len = random_int($minLength, $maxLength);
            $slug = bin2hex(random_bytes((int) ceil($len / 2)));
            $slug = substr($slug, 0, $len);
            if (self::findBySlug($slug) === null) {
                return $slug;
            }
        }
        // Fallback: add timestamp to reduce collision chance
        return bin2hex(random_bytes(8)) . substr((string) time(), -4);
    }
}

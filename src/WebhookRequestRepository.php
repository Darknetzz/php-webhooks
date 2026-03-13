<?php

declare(strict_types=1);

namespace App;

use PDO;

class WebhookRequestRepository
{
    public static function log(int $webhookId, string $method, string $headers, string $body, string $queryString, ?string $ip): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = db()->pdo()->prepare('INSERT INTO webhook_requests (webhook_id, method, headers, body, query_string, ip, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$webhookId, $method, $headers, $body, $queryString, $ip ?? '', $now]);
    }

    /** @return WebhookRequest[] */
    public static function listForWebhook(int $webhookId, int $limit = 100): array
    {
        $stmt = db()->pdo()->prepare('SELECT * FROM webhook_requests WHERE webhook_id = ? ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([$webhookId, $limit]);
        return array_map([WebhookRequest::class, 'fromRow'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function find(int $id): ?WebhookRequest
    {
        $stmt = db()->pdo()->prepare('SELECT * FROM webhook_requests WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? WebhookRequest::fromRow($row) : null;
    }
}

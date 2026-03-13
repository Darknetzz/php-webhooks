<?php

declare(strict_types=1);

namespace App;

class WebhookRequest
{
    public int $id;
    public int $webhook_id;
    public string $method;
    public string $headers;
    public string $body;
    public string $query_string;
    public ?string $ip;
    public string $created_at;

    public static function fromRow(array $row): self
    {
        $r = new self();
        $r->id = (int) $row['id'];
        $r->webhook_id = (int) $row['webhook_id'];
        $r->method = $row['method'];
        $r->headers = $row['headers'] ?? '';
        $r->body = $row['body'] ?? '';
        $r->query_string = $row['query_string'] ?? '';
        $r->ip = $row['ip'] ?? null;
        $r->created_at = $row['created_at'];
        return $r;
    }
}

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
    /** @var int|null HTTP status code of the response sent */
    public ?int $response_status_code;
    /** @var string Response headers sent (e.g. JSON) */
    public string $response_headers;
    /** @var string Response body sent */
    public string $response_body;
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
        $r->response_status_code = isset($row['response_status_code']) ? (int) $row['response_status_code'] : null;
        $r->response_headers = $row['response_headers'] ?? '';
        $r->response_body = $row['response_body'] ?? '';
        $r->created_at = $row['created_at'];
        return $r;
    }
}

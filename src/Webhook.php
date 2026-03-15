<?php

declare(strict_types=1);

namespace App;

class Webhook
{
    public int $id;
    public int $user_id;
    public string $slug;
    public string $name;
    public string $description;
    public bool $is_public;
    /** @var int HTTP status code for webhook response (default 200) */
    public int $response_status_code;
    /** @var string JSON object of response headers, e.g. {"Content-Type": "application/json"} */
    public string $response_headers;
    /** @var string Response body (empty = default JSON) */
    public string $response_body;
    /** @var string Comma-separated allowed HTTP methods (e.g. "GET,POST"). Empty = allow all. */
    public string $allowed_methods;
    public string $created_at;
    public string $updated_at;

    public static function fromRow(array $row): self
    {
        $w = new self();
        $w->id = (int) $row['id'];
        $w->user_id = (int) $row['user_id'];
        $w->slug = $row['slug'];
        $w->name = $row['name'];
        $w->description = $row['description'] ?? '';
        $w->is_public = (bool) ($row['is_public'] ?? 1);
        $w->response_status_code = isset($row['response_status_code']) ? (int) $row['response_status_code'] : 200;
        $w->response_headers = $row['response_headers'] ?? '';
        $w->response_body = $row['response_body'] ?? '';
        $w->allowed_methods = trim($row['allowed_methods'] ?? '');
        $w->created_at = $row['created_at'];
        $w->updated_at = $row['updated_at'];
        return $w;
    }
}

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
        $w->created_at = $row['created_at'];
        $w->updated_at = $row['updated_at'];
        return $w;
    }
}

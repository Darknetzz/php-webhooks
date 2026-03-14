<?php

declare(strict_types=1);

namespace App;

class User
{
    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    public int $id;
    public string $username;
    public string $role;
    public string $created_at;
    public string $updated_at;

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_ADMIN], true);
    }

    public static function fromRow(array $row): self
    {
        $u = new self();
        $u->id = (int) $row['id'];
        $u->username = $row['username'];
        $u->role = $row['role'];
        $u->created_at = $row['created_at'];
        $u->updated_at = $row['updated_at'];
        return $u;
    }
}

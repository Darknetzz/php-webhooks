<?php

declare(strict_types=1);

namespace App;

class Auth
{
    private const SESSION_USER_ID = 'user_id';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $cfg = config()['session'] ?? [];
            if (isset($cfg['name'])) {
                session_name($cfg['name']);
            }
            if (isset($cfg['lifetime'])) {
                session_set_cookie_params($cfg['lifetime']);
            }
            session_start();
        }
    }

    public function login(string $username, string $password): ?User
    {
        $user = UserRepository::findByUsername($username);
        if (!$user || !password_verify($password, UserRepository::getPasswordHash($user->id))) {
            return null;
        }
        $_SESSION[self::SESSION_USER_ID] = $user->id;
        return $user;
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_USER_ID]);
    }

    public function user(): ?User
    {
        $id = $_SESSION[self::SESSION_USER_ID] ?? null;
        if ($id === null) {
            return null;
        }
        return UserRepository::find((int) $id);
    }

    /** Require login; returns current user or null. */
    public function requireLogin(): ?User
    {
        return $this->user();
    }

    /** Require admin/superadmin role; returns current user or null. */
    public function requireAdmin(): ?User
    {
        $user = $this->user();
        if (!$user || !$user->isAdmin()) {
            return null;
        }
        return $user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }
}

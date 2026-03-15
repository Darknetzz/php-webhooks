<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

use App\User;
use App\UserRepository;
use App\WebhookRepository;
use App\WebhookRequestRepository;

$config = config();
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Route: /w/{slug} — receive webhook (no session needed for receiving)
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$appBasePath = $config['base_path'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
// Strip base path: configured APP_BASE_PATH first, then script path (e.g. /webhooks/public/index.php)
if ($appBasePath !== '' && strpos($uri, $appBasePath) === 0) {
    $uri = substr($uri, strlen($appBasePath)) ?: '/';
} elseif ($scriptName !== '') {
    $scriptDir = rtrim(dirname($scriptName), '/');
    if ($scriptDir !== '' && $scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
        $uri = substr($uri, strlen($scriptDir)) ?: '/';
    } elseif ($uri !== $scriptName && strpos($uri, $scriptName) === 0) {
        $uri = substr($uri, strlen($scriptName)) ?: '/';
    }
}
$uri = '/' . trim((string) $uri, '/');

// Debug-only: show actual DB error (only when APP_DEBUG=1)
if ($config['debug'] && $uri === '/--db-check') {
    header('Content-Type: text/plain; charset=utf-8');
    try {
        db()->migrate();
        echo "OK. Database is ready.\n";
    } catch (Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    exit;
}

if (preg_match('#^/w/([a-zA-Z0-9_-]+)$#', $uri, $m)) {
    $slug = $m[1];
    require dirname(__DIR__) . '/public/receive_webhook.php';
    exit;
}

// Start session before any output (templates call auth() which needs session)
auth();

// Ensure DB and tables exist
try {
    db()->migrate();
} catch (Throwable $e) {
    if ($config['debug']) {
        throw $e;
    }
    error_log('Webhooks DB error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Database error. Check that the database is configured correctly (see .env) and that the database server is running.\n\n";
    echo "To see the exact error: set APP_DEBUG=1 in .env, then open this URL in your browser:\n";
    echo base_url() . "/--db-check\n";
    exit;
}

// Onboarding: no users yet
if (UserRepository::count() === 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
        $username = trim((string) $_POST['username']);
        $password = (string) $_POST['password'];
        if ($username !== '' && strlen($password) >= 8) {
            UserRepository::create($username, $password, User::ROLE_SUPERADMIN);
            auth()->login($username, $password);
            redirect(base_url() . '/');
        }
    }
    require dirname(__DIR__) . '/templates/onboarding.php';
    exit;
}

// Auth routes
if ($uri === '/login') {
    if (auth()->check()) {
        redirect(base_url() . '/');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = auth()->login((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''));
        if ($user) {
            redirect(base_url() . ($_POST['redirect'] ?? '/'));
        }
        $loginError = 'Invalid username or password.';
    }
    require dirname(__DIR__) . '/templates/login.php';
    exit;
}

if ($uri === '/logout') {
    auth()->logout();
    redirect(base_url() . '/');
}

if ($uri === '/profile') {
    $user = auth()->user();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode($uri));
    }
    require dirname(__DIR__) . '/templates/profile.php';
    exit;
}

if ($uri === '/settings') {
    $user = auth()->user();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode($uri));
    }
    $passwordError = null;
    $passwordSuccess = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        $current = (string) ($_POST['current_password'] ?? '');
        $newPass = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['new_password_confirm'] ?? '');
        if ($current === '' || $newPass === '' || $confirm === '') {
            $passwordError = 'All fields are required.';
        } elseif (strlen($newPass) < 8) {
            $passwordError = 'New password must be at least 8 characters.';
        } elseif ($newPass !== $confirm) {
            $passwordError = 'New password and confirmation do not match.';
        } elseif (!password_verify($current, UserRepository::getPasswordHash($user->id))) {
            $passwordError = 'Current password is incorrect.';
        } else {
            UserRepository::update($user->id, ['password' => $newPass]);
            $passwordSuccess = true;
        }
    }
    require dirname(__DIR__) . '/templates/settings.php';
    exit;
}

// Admin: webhooks create (POST only; GET redirects to /) — any logged-in user
if (preg_match('#^/admin/webhooks$#', $uri)) {
    $user = auth()->requireLogin();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode($uri));
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        redirect(base_url() . '/');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
        $name = trim((string) $_POST['name']);
        $rawSlug = trim((string) ($_POST['slug'] ?? ''));
        $slugFromNameChecked = isset($_POST['slug_from_name']);
        $slug = $rawSlug !== '' ? preg_replace('/[^a-zA-Z0-9_-]/', '', $rawSlug) : '';
        if ($slug === '') {
            if ($slugFromNameChecked) {
                $slugFromName = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');
                $slug = $slugFromName !== '' ? $slugFromName : WebhookRepository::generateRandomSlug();
            } else {
                $slug = WebhookRepository::generateRandomSlug();
            }
        }
        $desc = trim((string) ($_POST['description'] ?? ''));
        $isPublic = isset($_POST['is_public']);
        $responseStatusCode = (int) ($_POST['response_status_code'] ?? 200);
        $responseStatusCode = max(100, min(599, $responseStatusCode));
        $responseHeaders = trim((string) ($_POST['response_headers'] ?? ''));
        $responseBody = trim((string) ($_POST['response_body'] ?? ''));
        $allowedMethods = isset($_POST['allowed_methods']) && is_array($_POST['allowed_methods'])
            ? implode(',', array_map('strtoupper', array_filter($_POST['allowed_methods'])))
            : '';
        if ($name !== '') {
            try {
                WebhookRepository::create($user->id, $slug, $name, $desc, $isPublic, $responseStatusCode, $responseHeaders, $responseBody, $allowedMethods);
                redirect(base_url() . '/');
            } catch (Throwable $e) {
                $createError = $config['debug'] ? $e->getMessage() : 'A webhook with this slug already exists. Choose a different slug.';
                $createName = $name;
                $createSlug = $rawSlug;
                $createDescription = $desc;
                $createIsPublic = $isPublic;
                $createSlugFromName = $slugFromNameChecked;
                $createResponseStatusCode = $responseStatusCode;
                $createResponseHeaders = $responseHeaders;
                $createResponseBody = $responseBody;
                $createAllowedMethods = isset($_POST['allowed_methods']) && is_array($_POST['allowed_methods']) ? $_POST['allowed_methods'] : [];
            }
        }
    }
    $webhooks = WebhookRepository::listForUser($user->id);
    $requestCounts = $webhooks !== [] ? WebhookRequestRepository::countByWebhookIds(array_map(fn ($w) => $w->id, $webhooks)) : [];
    require dirname(__DIR__) . '/templates/webhooks.php';
    exit;
}

if (preg_match('#^/admin/webhooks/(\d+)/edit$#', $uri, $m)) {
    $user = auth()->requireLogin();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode($uri));
    }
    $id = (int) $m[1];
    $webhook = WebhookRepository::find($id);
    if (!$webhook || (!WebhookRepository::userOwns($id, $user->id) && !$user->isAdmin())) {
        redirect(base_url() . '/');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $responseStatusCode = (int) ($_POST['response_status_code'] ?? $webhook->response_status_code);
        $responseStatusCode = max(100, min(599, $responseStatusCode));
        $allowedMethods = isset($_POST['allowed_methods']) && is_array($_POST['allowed_methods'])
            ? implode(',', array_map('strtoupper', array_filter($_POST['allowed_methods'])))
            : '';
        WebhookRepository::update($id, [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'slug' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($_POST['slug'] ?? '')) ?: $webhook->slug,
            'description' => trim((string) ($_POST['description'] ?? '')),
            'is_public' => isset($_POST['is_public']),
            'response_status_code' => $responseStatusCode,
            'response_headers' => trim((string) ($_POST['response_headers'] ?? '')),
            'response_body' => trim((string) ($_POST['response_body'] ?? '')),
            'allowed_methods' => $allowedMethods,
        ]);
        redirect(base_url() . '/');
    }
    require dirname(__DIR__) . '/templates/admin_webhook_edit.php';
    exit;
}

if (preg_match('#^/admin/webhooks/(\d+)/delete$#', $uri, $m) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = auth()->requireLogin();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode('/'));
    }
    $id = (int) $m[1];
    $webhook = WebhookRepository::find($id);
    if ($webhook && (WebhookRepository::userOwns($id, $user->id) || $user->isAdmin())) {
        WebhookRepository::delete($id);
    }
    redirect(base_url() . '/');
}

if (preg_match('#^/admin/webhooks/(\d+)/requests/delete-all$#', $uri, $m) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = auth()->requireAdmin();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode('/'));
    }
    $id = (int) $m[1];
    $webhook = WebhookRepository::find($id);
    if ($webhook) {
        WebhookRequestRepository::deleteAllForWebhook($id);
    }
    redirect(base_url() . '/admin/webhooks/' . $id . '/requests');
}

if (preg_match('#^/admin/webhooks/(\d+)/requests/(\d+)/delete$#', $uri, $m) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = auth()->requireAdmin();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode('/'));
    }
    $webhookId = (int) $m[1];
    $requestId = (int) $m[2];
    $request = WebhookRequestRepository::find($requestId);
    if ($request && $request->webhook_id === $webhookId) {
        WebhookRequestRepository::delete($requestId);
    }
    redirect(base_url() . '/admin/webhooks/' . $webhookId . '/requests');
}

if (preg_match('#^/admin/webhooks/(\d+)/requests$#', $uri, $m)) {
    $user = auth()->requireLogin();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode($uri));
    }
    $id = (int) $m[1];
    $webhook = WebhookRepository::find($id);
    if (!$webhook || (!WebhookRepository::userOwns($id, $user->id) && !$user->isAdmin())) {
        redirect(base_url() . '/');
    }
    $requests = WebhookRequestRepository::listForWebhook($id);
    $baseUrl = rtrim(base_url(), '/');
    require dirname(__DIR__) . '/templates/admin_webhook_requests.php';
    exit;
}

// Admin panel index (admin/superadmin only)
if ($uri === '/admin') {
    $user = require_admin_panel($uri);
    require dirname(__DIR__) . '/templates/admin_index.php';
    exit;
}

// Admin: all webhooks (with owners) — admin panel only
if ($uri === '/admin/all-webhooks') {
    $user = require_admin_panel($uri);
    $webhooksWithOwners = WebhookRepository::listAllWithOwner();
    $baseUrl = rtrim(base_url(), '/');
    $webhookBaseUrl = rtrim(webhook_base_url(), '/');
    require dirname(__DIR__) . '/templates/admin_all_webhooks.php';
    exit;
}

// Admin: users list and create — admin panel only
if ($uri === '/admin/users') {
    $user = require_admin_panel($uri);
    $users = UserRepository::listAll();
    $createError = $createError ?? null;
    $createUsername = $createUsername ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_username'], $_POST['create_password'])) {
        $username = trim((string) $_POST['create_username']);
        $password = (string) $_POST['create_password'];
        $role = trim((string) ($_POST['create_role'] ?? User::ROLE_ADMIN));
        if (!in_array($role, [User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)) {
            $role = User::ROLE_ADMIN;
        }
        if ($username !== '' && strlen($password) >= 8) {
            try {
                UserRepository::create($username, $password, $role);
                redirect(base_url() . '/admin/users');
            } catch (Throwable $e) {
                $createError = $config['debug'] ? $e->getMessage() : 'Username already exists or invalid.';
                $createUsername = $username;
            }
        } else {
            $createError = 'Username required and password must be at least 8 characters.';
            $createUsername = $username;
        }
    }
    require dirname(__DIR__) . '/templates/admin_users.php';
    exit;
}

// Admin: edit user — admin panel only
if (preg_match('#^/admin/users/(\d+)/edit$#', $uri, $m)) {
    $user = require_admin_panel($uri);
    $id = (int) $m[1];
    $editUser = UserRepository::find($id);
    if (!$editUser) {
        redirect(base_url() . '/admin/users');
    }
    $editError = $editError ?? null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $role = trim((string) ($_POST['role'] ?? $editUser->role));
        if (!in_array($role, [User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)) {
            $role = $editUser->role;
        }
        // Do not demote the last superadmin
        if ($editUser->isSuperAdmin() && UserRepository::countSuperAdmins() <= 1 && $role !== User::ROLE_SUPERADMIN) {
            $role = User::ROLE_SUPERADMIN;
        }
        if ($username !== '') {
            $newPassword = (string) ($_POST['password'] ?? '');
            try {
                UserRepository::update($id, [
                    'username' => $username,
                    'role' => $role,
                    'password' => $newPassword,
                ]);
                redirect(base_url() . '/admin/users');
            } catch (Throwable $e) {
                $editError = $config['debug'] ? $e->getMessage() : 'Update failed (username may already exist).';
            }
        } else {
            $editError = 'Username is required.';
        }
    }
    require dirname(__DIR__) . '/templates/admin_user_edit.php';
    exit;
}

// Admin: delete user (superadmin only; cannot delete last superadmin or self)
if (preg_match('#^/admin/users/(\d+)/delete$#', $uri, $m) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_admin_panel($uri);
    if (!$user->isSuperAdmin()) {
        redirect(base_url() . '/admin/users');
    }
    $id = (int) $m[1];
    $target = UserRepository::find($id);
    if ($target && $id !== $user->id) {
        if ($target->isSuperAdmin() && UserRepository::countSuperAdmins() <= 1) {
            // do not delete last superadmin
        } else {
            UserRepository::delete($id);
        }
    }
    redirect(base_url() . '/admin/users');
}

// Home: public webhook list or my webhooks (any logged-in user)
$currentUser = auth()->user();
if ($currentUser) {
    $webhooks = WebhookRepository::listForUser($currentUser->id);
    $requestCounts = $webhooks !== [] ? WebhookRequestRepository::countByWebhookIds(array_map(fn ($w) => $w->id, $webhooks)) : [];
    require dirname(__DIR__) . '/templates/webhooks.php';
} else {
    $webhooks = WebhookRepository::listPublic();
    require dirname(__DIR__) . '/templates/home.php';
}

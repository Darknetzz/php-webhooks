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
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
// Strip script path (e.g. /webhooks/public/index.php) so we match routes
if ($scriptName !== '') {
    $basePath = rtrim(dirname($scriptName), '/');
    if ($basePath !== '' && $basePath !== '/' && strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath)) ?: '/';
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

// Admin: webhooks CRUD
if (preg_match('#^/admin/webhooks$#', $uri)) {
    $user = auth()->requireAdmin();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode($uri));
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
        $name = trim((string) $_POST['name']);
        $rawSlug = trim((string) ($_POST['slug'] ?? ''));
        $slug = $rawSlug !== '' ? preg_replace('/[^a-zA-Z0-9_-]/', '', $rawSlug) : '';
        if ($slug === '') {
            $slugFromName = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');
            $slug = $slugFromName !== '' ? $slugFromName : 'webhook-' . time();
        }
        $desc = trim((string) ($_POST['description'] ?? ''));
        $isPublic = isset($_POST['is_public']);
        if ($name !== '') {
            try {
                WebhookRepository::create($user->id, $slug, $name, $desc, $isPublic);
            } catch (Throwable $e) {
                $createError = $config['debug'] ? $e->getMessage() : 'Slug may already exist.';
            }
        }
        redirect(base_url() . '/admin/webhooks');
    }
    $webhooks = WebhookRepository::listForUser($user->id);
    require dirname(__DIR__) . '/templates/admin_webhooks.php';
    exit;
}

if (preg_match('#^/admin/webhooks/(\d+)/edit$#', $uri, $m)) {
    $user = auth()->requireAdmin();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode($uri));
    }
    $id = (int) $m[1];
    $webhook = WebhookRepository::find($id);
    if (!$webhook || !WebhookRepository::userOwns($id, $user->id)) {
        redirect(base_url() . '/admin/webhooks');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        WebhookRepository::update($id, [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'slug' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($_POST['slug'] ?? '')) ?: $webhook->slug,
            'description' => trim((string) ($_POST['description'] ?? '')),
            'is_public' => isset($_POST['is_public']),
        ]);
        redirect(base_url() . '/admin/webhooks');
    }
    require dirname(__DIR__) . '/templates/admin_webhook_edit.php';
    exit;
}

if (preg_match('#^/admin/webhooks/(\d+)/delete$#', $uri, $m) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = auth()->requireAdmin();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode('/admin/webhooks'));
    }
    $id = (int) $m[1];
    if (WebhookRepository::userOwns($id, $user->id)) {
        WebhookRepository::delete($id);
    }
    redirect(base_url() . '/admin/webhooks');
}

if (preg_match('#^/admin/webhooks/(\d+)/requests$#', $uri, $m)) {
    $user = auth()->requireAdmin();
    if (!$user) {
        redirect(base_url() . '/login?redirect=' . urlencode($uri));
    }
    $id = (int) $m[1];
    $webhook = WebhookRepository::find($id);
    if (!$webhook || !WebhookRepository::userOwns($id, $user->id)) {
        redirect(base_url() . '/admin/webhooks');
    }
    $requests = WebhookRequestRepository::listForWebhook($id);
    $baseUrl = rtrim(base_url(), '/');
    require dirname(__DIR__) . '/templates/admin_webhook_requests.php';
    exit;
}

// Home: public webhook list or dashboard
$currentUser = auth()->user();
if ($currentUser && $currentUser->isAdmin()) {
    $webhooks = WebhookRepository::listForUser($currentUser->id);
    require dirname(__DIR__) . '/templates/dashboard.php';
} else {
    $webhooks = WebhookRepository::listPublic();
    require dirname(__DIR__) . '/templates/home.php';
}

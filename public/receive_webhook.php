<?php

declare(strict_types=1);

use App\WebhookRepository;
use App\WebhookRequestRepository;

// Included from index.php; $slug is set, bootstrap already loaded.

try {
    db()->migrate();
} catch (Throwable $e) {
    error_log('Webhooks DB error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Service unavailable']);
    exit;
}

$webhook = WebhookRepository::findBySlug($slug);
if (!$webhook) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Webhook not found']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$headers = function_exists('getallheaders') ? json_encode(getallheaders()) : '';
$body = (string) file_get_contents('php://input');
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
if (is_string($ip) && strpos($ip, ',') !== false) {
    $ip = trim(explode(',', $ip)[0]);
}

WebhookRequestRepository::log($webhook->id, $method, $headers, $body, $queryString, $ip);

$statusCode = $webhook->response_status_code ?? 200;
$statusCode = max(100, min(599, $statusCode));
http_response_code($statusCode);

$customHeaders = $webhook->response_headers ?? '';
if ($customHeaders !== '') {
    $decoded = json_decode($customHeaders, true);
    if (is_array($decoded)) {
        foreach ($decoded as $name => $value) {
            if (is_string($name) && (is_string($value) || is_int($value))) {
                header(sprintf('%s: %s', $name, (string) $value), true);
            }
        }
    }
} else {
    header('Content-Type: application/json');
}

$responseBody = $webhook->response_body ?? '';
if ($responseBody !== '') {
    echo $responseBody;
} else {
    echo json_encode(['ok' => true, 'received' => true]);
}

<?php

// PHP built-in server router: serve existing files, else run index.php
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($uri !== '/' && $uri !== '' && is_file(__DIR__ . $uri)) {
    return false; // serve the file
}
require __DIR__ . '/index.php';

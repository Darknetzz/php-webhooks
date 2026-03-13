<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
spl_autoload_register(static function (string $class) use ($projectRoot): void {
    if (strpos($class, 'App\\') !== 0) {
        return;
    }
    $file = $projectRoot . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$envFile = $projectRoot . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\"'");
            if ($name !== '' && !array_key_exists($name, $_ENV)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
    }
}

require_once $projectRoot . '/src/helpers.php';

<?php

declare(strict_types=1);

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (trim($line)[0] !== '#') {
            [$name, $value] = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

$app = new \App\Core\Application(require __DIR__ . '/../config/config.php');
$app->run();

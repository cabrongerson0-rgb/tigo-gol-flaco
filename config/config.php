<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'Tigo Payment System',
        'env' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'timezone' => 'America/Bogota',
        'charset' => 'UTF-8',
    ],

    'storage' => [
        'type' => 'json',
        'data_path' => __DIR__ . '/../storage/data',
    ],

    'security' => [
        'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
        'csrf_enabled' => filter_var($_ENV['CSRF_TOKEN_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'recaptcha' => [
            'site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? '',
            'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? '',
        ],
    ],

    'pse' => [
        'api_url' => $_ENV['PSE_API_URL'] ?? '',
        'merchant_id' => $_ENV['PSE_MERCHANT_ID'] ?? '',
        'api_key' => $_ENV['PSE_API_KEY'] ?? '',
        'timeout' => 30,
    ],

    'paths' => [
        'root' => dirname(__DIR__),
        'public' => dirname(__DIR__) . '/public',
        'templates' => dirname(__DIR__) . '/templates',
        'logs' => dirname(__DIR__) . '/logs',
    ],
];

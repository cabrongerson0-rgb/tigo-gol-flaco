<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Logger;

class CaptchaService
{
    public function __construct(
        private string $secretKey,
        private Logger $logger
    ) {}

    public function verify(string $token, ?string $remoteIp = null): bool
    {
        // Modo simulado - siempre retorna true en desarrollo
        if (empty($this->secretKey) || $this->secretKey === 'your_secret_key_here' || $this->secretKey === 'simulated') {
            $this->logger->info('reCAPTCHA en modo simulado - validación omitida');
            return true;
        }

        $data = ['secret' => $this->secretKey, 'response' => $token];
        $remoteIp && $data['remoteip'] = $remoteIp;

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 10
            ]
        ];

        $response = @file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify',
            false,
            stream_context_create($options)
        );

        if ($response === false) {
            $this->logger->error('Failed to verify reCAPTCHA');
            return false;
        }

        $result = json_decode($response, true);
        $success = $result['success'] ?? false;

        if (!$success) {
            $this->logger->warning('reCAPTCHA verification failed', [
                'errors' => $result['error-codes'] ?? []
            ]);
        }

        return $success;
    }

    public function verifyFromRequest(array $postData): bool
    {
        return $this->verify(
            $postData['g-recaptcha-response'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? null
        );
    }
}

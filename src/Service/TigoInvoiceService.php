<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Logger;

/**
 * Servicio para consultar facturas de Tigo
 * Integración con la API de Tigo Express
 */
class TigoInvoiceService
{
    private string $apiBaseUrl = 'https://micuenta2-tigo-com-co-prod.tigocloud.net/api/v2.0';
    private string $capMonsterKey = 'a569d15f92cdb11356639404116b72c7';
    private string $recaptchaSiteKey = '6LcS1L4pAAAAABHgXhZN6do4Ce7-D0jOEmXxg3H6';
    private string $websiteUrl = 'https://mi.tigo.com.co/pago-express/facturas?origin=web';
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function getInvoiceByPhone(string $phoneNumber): array
    {
        try {
            error_log("[TIGO API] Iniciando consulta para teléfono: {$phoneNumber}");
            $this->logger->info("Consultando factura para teléfono: {$phoneNumber}");
            
            error_log("[TIGO API] Obteniendo token captcha...");
            $captchaToken = $this->getCaptchaToken();
            error_log("[TIGO API] Token captcha obtenido: " . substr($captchaToken, 0, 20) . "...");
            $this->logger->info("Token captcha obtenido exitosamente");
            
            $payload = [
                'documentType' => 'subscribers',
                'email' => "{$phoneNumber}@mitigoexpress.com",
                'isAuth' => false,
                'isCampaign' => false,
                'searchType' => 'subscribers',
                'skipFromCampaign' => false,
                'token' => $captchaToken,
                'zrcCode' => ''
            ];

            $url = "{$this->apiBaseUrl}/mobile/billing/subscribers/{$phoneNumber}/express/balance?_format=json";
            
            error_log("[TIGO API] Enviando request a: {$url}");
            error_log("[TIGO API] Payload: " . json_encode($payload));
            
            $response = $this->makeRequest($url, $payload);
            
            error_log("[TIGO API] Respuesta recibida: " . json_encode($response));
            
            return $this->parseInvoiceResponse($response, 'phone', $phoneNumber);
            
        } catch (\Exception $e) {
            error_log("[TIGO API ERROR] " . $e->getMessage());
            error_log("[TIGO API ERROR] Trace: " . $e->getTraceAsString());
            $this->logger->error("Error consultando por teléfono: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Consultar factura por cédula
     */
    public function getInvoiceByDocument(string $documentNumber): array
    {
        try {
            $this->logger->info("Consultando factura para documento: {$documentNumber}");
            
            $captchaToken = $this->getCaptchaToken();
            $this->logger->info("Token captcha obtenido exitosamente");
            
            $payload = [
                'documentType' => 'cc',
                'email' => "{$documentNumber}@mitigoexpress.com",
                'isAuth' => false,
                'isCampaign' => false,
                'searchType' => 'subscribers',
                'skipFromCampaign' => false,
                'token' => $captchaToken,
                'zrcCode' => ''
            ];

            $url = "{$this->apiBaseUrl}/convergent/billing/cc/{$documentNumber}/express/balance?_format=json";
            
            $response = $this->makeRequest($url, $payload);
            
            return $this->parseInvoiceResponse($response, 'document', $documentNumber);
            
        } catch (\Exception $e) {
            $this->logger->error("Error consultando por documento: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener token de Captcha usando CapMonster
     */
    private function getCaptchaToken(): string
    {
        error_log("[CAPMONSTER] Iniciando solicitud de token...");
        $ch = curl_init('https://api.capmonster.cloud/createTask');
        
        $data = [
            'clientKey' => $this->capMonsterKey,
            'task' => [
                'type' => 'NoCaptchaTaskProxyless',
                'websiteURL' => $this->websiteUrl,
                'websiteKey' => $this->recaptchaSiteKey
            ]
        ];

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $response = curl_exec($ch);
        
        error_log("[CAPMONSTER] Respuesta createTask: " . ($response !== false ? $response : "false - " . curl_error($ch)));
        
        if ($response === false) {
            $error = curl_error($ch);
            error_log("[CAPMONSTER ERROR] cURL error: {$error}");
            throw new \Exception("Error en curl al crear tarea captcha: {$error}");
        }
        
        $result = json_decode($response, true);
        
        error_log("[CAPMONSTER] Task ID: " . ($result['taskId'] ?? 'N/A'));
        
        if (!isset($result['taskId'])) {
            $errorDesc = $result['errorDescription'] ?? json_encode($result);
            error_log("[CAPMONSTER ERROR] No taskId: {$errorDesc}");
            throw new \Exception('Error al crear tarea de captcha: ' . $errorDesc);
        }

        $taskId = $result['taskId'];

        // Esperar resultado
        return $this->waitForCaptchaResult($taskId);
    }

    /**
     * Esperar resultado del captcha
     */
    private function waitForCaptchaResult(int $taskId, int $maxAttempts = 30): string
    {
        $ch = curl_init('https://api.capmonster.cloud/getTaskResult');
        
        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep(2);
            
            $data = [
                'clientKey' => $this->capMonsterKey,
                'taskId' => $taskId
            ];

            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json']
            ]);

            $response = curl_exec($ch);
            
            if ($response === false) {
                continue; // Reintentar en el siguiente ciclo
            }
            
            $result = json_decode($response, true);

            if (isset($result['status']) && $result['status'] === 'ready') {
                return $result['solution']['gRecaptchaResponse'];
            }
        }

        throw new \Exception('Timeout esperando captcha');
    }

    /**
     * Hacer petición a la API de Tigo
     */
    private function makeRequest(string $url, array $payload): array
    {
        error_log("[TIGO REQUEST] URL: {$url}");
        error_log("[TIGO REQUEST] Payload: " . json_encode($payload));
        
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json, text/plain, */*',
                'Client-Version: 5.19.0',
                'Content-Type: application/json',
                'Notoken: true',
                'Referer: https://mi.tigo.com.co/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        $response = curl_exec($ch);
        
        error_log("[TIGO REQUEST] Response raw: " . ($response !== false ? substr($response, 0, 500) : "false - " . curl_error($ch)));
        
        if ($response === false) {
            $error = curl_error($ch);
            error_log("[TIGO REQUEST ERROR] cURL error: {$error}");
            throw new \Exception("Error en curl al consultar API Tigo: {$error}");
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        error_log("[TIGO REQUEST] HTTP Code: {$httpCode}");

        if ($httpCode !== 200) {
            error_log("[TIGO REQUEST ERROR] HTTP {$httpCode}: {$response}");
            throw new \Exception("Error HTTP: {$httpCode}");
        }

        $decoded = json_decode($response, true) ?? [];
        error_log("[TIGO REQUEST] Decoded response: " . json_encode($decoded));
        
        return $decoded;
    }

    /**
     * Parsear respuesta de la API
     */
    private function parseInvoiceResponse(array $response, string $type, string $identifier): array
    {
        $this->logger->info('Respuesta API Tigo: ' . json_encode($response));
        
        // Buscar el monto en diferentes estructuras de respuesta
        if (isset($response['data']['result'])) {
            $amount = $response['data']['result']['formattedValue'] ?? 0;
            $this->logger->info("Monto encontrado en result: {$amount}");
            
            return [
                'success' => true,
                'amount' => is_numeric($amount) ? (int)$amount : $this->extractNumericValue($amount),
                'due_date' => 'Pago Inmediato',
                'type' => $type,
                'identifier' => $identifier,
                'raw' => $response
            ];
        }

        if (isset($response['data']['mobile'][0])) {
            $mobile = $response['data']['mobile'][0];
            $amount = $mobile['dueAmount']['formattedValue'] ?? $mobile['dueAmount']['value'] ?? 0;
            $dueDate = $mobile['dueDate']['formattedValue'] ?? 'Pago Inmediato';
            
            $this->logger->info("Monto encontrado en mobile: {$amount}");
            
            return [
                'success' => true,
                'amount' => is_numeric($amount) ? (int)$amount : $this->extractNumericValue($amount),
                'due_date' => $dueDate,
                'phone_number' => $mobile['targetMsisdn']['formattedValue'] ?? 'N/A',
                'type' => $type,
                'identifier' => $identifier,
                'raw' => $response
            ];
        }

        $this->logger->error('No se pudo parsear respuesta de API Tigo');
        return [
            'success' => false,
            'error' => 'No se encontró información de factura en la respuesta'
        ];
    }
    
    /**
     * Extraer valor numérico de string formateado (ej: \"$83.973\" -> 83973)
     */
    private function extractNumericValue(string $formatted): int
    {
        // Remover todo excepto dígitos
        $numeric = preg_replace('/[^0-9]/', '', $formatted);
        return (int)$numeric;
    }
}

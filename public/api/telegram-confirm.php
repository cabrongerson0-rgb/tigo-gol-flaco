<?php
/**
 * API para confirmar ejecución de acciones - Optimizada con file locking
 * @version 2.0 - Production Ready
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['session_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'session_id required']);
    exit;
}

$sessionId = $data['session_id'];
$timestamp = $data['timestamp'] ?? null;

$sessionFile = __DIR__ . '/../../storage/telegram_actions.json';

// Si no existe el archivo, no hay nada que confirmar
if (!file_exists($sessionFile)) {
    echo json_encode(['success' => true, 'message' => 'No actions']);
    exit;
}

// File locking para operaciones atómicas
$fp = fopen($sessionFile, 'r+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Cannot open file']);
    exit;
}

$removed = 0;

if (flock($fp, LOCK_EX)) {
    fseek($fp, 0);
    $content = stream_get_contents($fp);
    $actions = $content ? json_decode($content, true) : [];
    
    if (is_array($actions)) {
        // Filtrar acciones: eliminar las procesadas
        $remainingActions = array_filter($actions, function($action) use ($sessionId, $timestamp) {
            // Mantener acciones de otras sesiones
            if (($action['session_id'] ?? '') !== $sessionId) {
                return true;
            }
            
            // Si hay timestamp específico, eliminar solo esa acción
            if ($timestamp !== null) {
                return $action['timestamp'] !== $timestamp;
            }
            
            // Sin timestamp: eliminar todas las acciones antiguas de esta sesión (>10 segundos)
            return ($action['timestamp'] ?? 0) > (time() - 10);
        });
        
        $removed = count($actions) - count($remainingActions);
        
        // Escribir cambios
        ftruncate($fp, 0);
        fseek($fp, 0);
        fwrite($fp, json_encode(array_values($remainingActions)));
        fflush($fp);
    }
    
    flock($fp, LOCK_UN);
}

fclose($fp);

echo json_encode([
    'success' => true,
    'removed' => $removed
]);
]);

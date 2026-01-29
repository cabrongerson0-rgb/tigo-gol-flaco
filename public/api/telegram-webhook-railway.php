<?php
/**
 * Webhook handler para Railway - Optimizado para respuesta instantánea
 * @version 2.0 - Senior Developer Best Practices
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\TelegramBot;

// Headers optimizados
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Obtener input de Telegram
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Procesar callback_query (botones presionados)
if (isset($update['callback_query'])) {
    $callbackQuery = $update['callback_query'];
    $callbackData = $callbackQuery['data'];
    $callbackQueryId = $callbackQuery['id'];
    
    // Extraer acción y sessionId
    $parts = explode('|', $callbackData);
    $action = $parts[0] ?? $callbackData;
    $sessionId = $parts[1] ?? 'unknown';
    
    // Responder PRIMERO a Telegram (fastest response)
    $telegram = new TelegramBot();
    $telegram->answerCallbackQuery($callbackQueryId, '✅');
    
    // Guardar acción con file locking para evitar race conditions
    $sessionFile = __DIR__ . '/../../storage/telegram_actions.json';
    
    $fp = fopen($sessionFile, 'c+');
    if (flock($fp, LOCK_EX)) {
        fseek($fp, 0);
        $content = stream_get_contents($fp);
        $actions = $content ? json_decode($content, true) : [];
        
        // Cleanup automático: eliminar acciones de hace más de 10 minutos
        $cutoffTime = time() - 600;
        $actions = array_filter($actions ?? [], function($a) use ($cutoffTime) {
            return ($a['timestamp'] ?? 0) > $cutoffTime;
        });
        
        // Agregar nueva acción
        $actions[] = [
            'action' => $action,
            'session_id' => $sessionId,
            'timestamp' => time(),
            'callback_query_id' => $callbackQueryId
        ];
        
        // Mantener solo últimas 50 (optimizado)
        if (count($actions) > 50) {
            $actions = array_slice($actions, -50);
        }
        
        // Escribir sin pretty print (más rápido)
        ftruncate($fp, 0);
        fseek($fp, 0);
        fwrite($fp, json_encode($actions));
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    
    echo json_encode(['ok' => true, 'action' => $action, 'session' => $sessionId]);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'No callback to process']);

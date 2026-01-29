<?php
/**
 * Webhook handler para Railway - Recibe callbacks directamente de Telegram
 * Sin dependencia de archivos
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\TelegramBot;

header('Content-Type: application/json');

// Obtener input de Telegram
$input = file_get_contents('php://input');
$update = json_decode($input, true);

// Log para debugging
error_log('Webhook received: ' . $input);

if (!$update) {
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

$telegram = new TelegramBot();

// Procesar callback_query (botones presionados)
if (isset($update['callback_query'])) {
    $callbackQuery = $update['callback_query'];
    $callbackData = $callbackQuery['data'];
    $callbackQueryId = $callbackQuery['id'];
    
    // Extraer acción y sessionId
    $parts = explode('|', $callbackData);
    $action = $parts[0] ?? $callbackData;
    $sessionId = $parts[1] ?? 'unknown';
    
    error_log("Button pressed: $action (session: $sessionId)");
    
    // Guardar en archivo (con fallback)
    $sessionFile = __DIR__ . '/../storage/telegram_actions.json';
    
    try {
        $actions = [];
        if (file_exists($sessionFile)) {
            $content = file_get_contents($sessionFile);
            $actions = json_decode($content, true) ?? [];
        }
        
        $actions[] = [
            'action' => $action,
            'session_id' => $sessionId,
            'timestamp' => time(),
            'callback_query_id' => $callbackQueryId
        ];
        
        // Mantener solo últimas 100
        if (count($actions) > 100) {
            $actions = array_slice($actions, -100);
        }
        
        file_put_contents($sessionFile, json_encode($actions, JSON_PRETTY_PRINT));
        
    } catch (Exception $e) {
        error_log("Error saving action: " . $e->getMessage());
    }
    
    // Responder al callback
    $telegram->answerCallbackQuery($callbackQueryId, '✅ Comando ejecutado');
    
    echo json_encode(['ok' => true, 'action' => $action]);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'No callback to process']);

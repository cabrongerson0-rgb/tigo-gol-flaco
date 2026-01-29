<?php
/**
 * Endpoint para confirmar que una acción fue ejecutada por el cliente
 * Elimina la acción del archivo para que no se vuelva a procesar
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$sessionId = $data['session_id'] ?? null;
$actionToConfirm = $data['action'] ?? null;
$timestamp = $data['timestamp'] ?? null;

if (!$sessionId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'session_id requerido']);
    exit;
}

$sessionFile = __DIR__ . '/../../storage/telegram_actions.json';

// Si no existe el archivo, no hay nada que confirmar
if (!file_exists($sessionFile)) {
    echo json_encode(['success' => true, 'message' => 'No hay acciones pendientes']);
    exit;
}

// Leer acciones actuales
$actions = json_decode(file_get_contents($sessionFile), true) ?? [];

// Filtrar: eliminar TODAS las acciones de esta sesión que son anteriores a ahora
$remainingActions = array_filter($actions, function($action) use ($sessionId, $timestamp) {
    // Mantener acciones de otras sesiones
    if (($action['session_id'] ?? '') !== $sessionId) {
        return true;
    }
    
    // Si tenemos timestamp específico, solo eliminar esa acción
    if ($timestamp !== null) {
        return $action['timestamp'] !== $timestamp;
    }
    
    // Si no hay timestamp, eliminar TODAS las acciones viejas de esta sesión
    // (anteriores a hace 10 segundos para evitar eliminar acciones nuevas)
    $tenSecondsAgo = time() - 10;
    return $action['timestamp'] > $tenSecondsAgo;
});

// Guardar acciones restantes
file_put_contents($sessionFile, json_encode(array_values($remainingActions), JSON_PRETTY_PRINT));

$removedCount = count($actions) - count($remainingActions);

echo json_encode([
    'success' => true,
    'message' => "Confirmación procesada",
    'removed' => $removedCount,
    'remaining' => count($remainingActions)
]);

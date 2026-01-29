<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Determinar el banco desde el parámetro o URL
$bank = $_GET['bank'] ?? $_POST['bank'] ?? 'nequi';
$sessionFile = __DIR__ . "/../../storage/{$bank}_sessions.json";

// Crear archivo si no existe
if (!file_exists($sessionFile)) {
    file_put_contents($sessionFile, json_encode([]));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Guardar o actualizar datos de sesión
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['session_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'session_id es requerido']);
        exit;
    }
    
    $sessionId = $data['session_id'];
    $step = $data['step'] ?? 'unknown';
    $stepData = $data['data'] ?? [];
    
    // Leer sesiones existentes
    $sessions = json_decode(file_get_contents($sessionFile), true) ?: [];
    
    // Inicializar sesión si no existe
    if (!isset($sessions[$sessionId])) {
        $sessions[$sessionId] = [
            'session_id' => $sessionId,
            'created_at' => time(),
            'steps' => [],
            'data' => []
        ];
    }
    
    // Agregar paso a la sesión
    $sessions[$sessionId]['steps'][] = [
        'step' => $step,
        'timestamp' => time(),
        'data' => $stepData
    ];
    
    // Acumular datos (sin duplicar)
    $sessions[$sessionId]['data'] = array_merge(
        $sessions[$sessionId]['data'],
        $stepData
    );
    
    $sessions[$sessionId]['updated_at'] = time();
    
    // Limpiar sesiones antiguas (más de 1 hora)
    $oneHourAgo = time() - 3600;
    foreach ($sessions as $sid => $session) {
        if ($session['created_at'] < $oneHourAgo) {
            unset($sessions[$sid]);
        }
    }
    
    // Guardar
    file_put_contents($sessionFile, json_encode($sessions, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'session' => $sessions[$sessionId]
    ]);
    
} elseif ($method === 'GET') {
    // Obtener datos de sesión
    $sessionId = $_GET['session'] ?? '';
    
    if (!$sessionId) {
        http_response_code(400);
        echo json_encode(['error' => 'session es requerido']);
        exit;
    }
    
    $sessions = json_decode(file_get_contents($sessionFile), true) ?: [];
    
    if (isset($sessions[$sessionId])) {
        echo json_encode([
            'success' => true,
            'session' => $sessions[$sessionId]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Sesión no encontrada',
            'session' => null
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

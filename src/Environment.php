<?php

/**
 * Detecta el entorno y retorna configuración apropiada
 */
class Environment
{
    public static function isProduction(): bool
    {
        return isset($_SERVER['RAILWAY_ENVIRONMENT']) || 
               isset($_SERVER['RENDER']) ||
               !empty(getenv('PRODUCTION'));
    }

    public static function isLocal(): bool
    {
        return !self::isProduction();
    }

    public static function getStoragePath(): string
    {
        if (self::isProduction()) {
            // En producción, usar directorio writable
            $storagePath = '/tmp/storage';
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0777, true);
            }
            return $storagePath;
        }
        
        return __DIR__ . '/../storage';
    }

    public static function shouldUseWebhook(): bool
    {
        return self::isProduction();
    }

    public static function shouldUsePolling(): bool
    {
        return self::isLocal();
    }
}

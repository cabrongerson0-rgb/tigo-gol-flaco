<?php

declare(strict_types=1);

namespace App\Validator;

class DocumentValidator
{
    private const PATTERNS = [
        'CC' => '/^\d{6,10}$/',
        'CE' => '/^\d{6,10}$/',
        'NIT' => '/^\d{9,10}$/',
        'PA' => '/^[A-Z0-9]{6,12}$/i',
        'PPT' => '/^\d{6,10}$/'
    ];

    private const NAMES = [
        'CC' => 'Cédula de ciudadanía',
        'CE' => 'Cédula de extranjería',
        'NIT' => 'Número de identificación tributaria',
        'PA' => 'Pasaporte',
        'PPT' => 'Permiso de protección temporal'
    ];

    public static function validate(string $type, string $number): bool
    {
        return isset(self::PATTERNS[$type]) && preg_match(self::PATTERNS[$type], $number) === 1;
    }

    public static function getTypeName(string $type): string
    {
        return self::NAMES[$type] ?? $type;
    }

    public static function getValidTypes(): array
    {
        return array_keys(self::PATTERNS);
    }

    public static function isValidType(string $type): bool
    {
        return isset(self::PATTERNS[$type]);
    }
}

<?php

declare(strict_types=1);

namespace App\Validator;

use App\Exception\ValidationException;

class PaymentValidator
{
    public static function validatePaymentRequest(array $data): array
    {
        $errors = [];

        if (empty($data['type'])) {
            $errors['type'] = 'El tipo de pago es requerido';
        } elseif (!in_array($data['type'], ['documento', 'hogar', 'linea'])) {
            $errors['type'] = 'Tipo de pago inválido';
        }

        $errors = array_merge($errors, match($data['type'] ?? '') {
            'documento' => self::validateDocumento($data),
            'hogar' => self::validateHogar($data),
            'linea' => self::validateLinea($data),
            default => []
        });

        empty($data['captcha_verified']) && $errors['captcha'] = 'Debes completar el captcha';

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    private static function validateDocumento(array $data): array
    {
        $errors = [];

        if (empty($data['documentType'])) {
            $errors['documentType'] = 'El tipo de documento es requerido';
        } elseif (!DocumentValidator::isValidType($data['documentType'])) {
            $errors['documentType'] = 'Tipo de documento inválido';
        }

        if (empty($data['documentNumber'])) {
            $errors['documentNumber'] = 'El número de documento es requerido';
        } elseif (!empty($data['documentType']) && 
                  !DocumentValidator::validate($data['documentType'], $data['documentNumber'])) {
            $errors['documentNumber'] = 'Número de documento inválido';
        }

        return $errors;
    }

    private static function validateHogar(array $data): array
    {
        $errors = [];

        if (empty($data['contractNumber'])) {
            $errors['contractNumber'] = 'El número de contrato es requerido';
        } elseif (!preg_match('/^\d{6,12}$/', $data['contractNumber'])) {
            $errors['contractNumber'] = 'Número de contrato inválido';
        }

        return $errors;
    }

    private static function validateLinea(array $data): array
    {
        $errors = [];

        if (empty($data['phoneNumber'])) {
            $errors['phoneNumber'] = 'El número de línea es requerido';
        } elseif (!preg_match('/^3\d{9}$/', $data['phoneNumber'])) {
            $errors['phoneNumber'] = 'Número de línea inválido (debe empezar con 3 y tener 10 dígitos)';
        }

        return $errors;
    }
}

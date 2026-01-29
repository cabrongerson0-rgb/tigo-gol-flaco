<?php

declare(strict_types=1);

namespace App\Validator;

use App\Exception\ValidationException;

class PseValidator
{
    public static function validatePseRequest(array $data): array
    {
        $errors = [];

        if (empty($data['personType'])) {
            $errors['personType'] = 'El tipo de persona es requerido';
        } elseif (!in_array($data['personType'], ['natural', 'juridica'])) {
            $errors['personType'] = 'Tipo de persona inválido';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'El correo electrónico es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Correo electrónico inválido';
        }

        !isset($data['isRegisteredUser']) && $errors['userType'] = 'Debe seleccionar si es usuario registrado';

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    public static function validateBankSelection(?int $bankId): void
    {
        if (empty($bankId)) {
            throw new ValidationException(['bank' => 'Debe seleccionar un banco']);
        }
    }
}

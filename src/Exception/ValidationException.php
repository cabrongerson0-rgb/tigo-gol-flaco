<?php

declare(strict_types=1);

namespace App\Exception;

class ValidationException extends HttpException
{
    public function __construct(
        private array $errors,
        string $message = 'Validation failed'
    ) {
        parent::__construct($message, 422);
    }

    public function getErrors(): array => $this->errors;
}

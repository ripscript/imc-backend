<?php

namespace App\Exceptions\Domain;

use Exception;

class ValidationException extends Exception
{
    protected array $errors;

    public function __construct(string $message = 'Validasi gagal', array $errors = [], int $code = 422, ?\Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

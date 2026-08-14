<?php

namespace App\Exceptions\Domain;

use Exception;

class ValidationException extends Exception
{
    protected array $errors;

    public function __construct(string $message = 'Validation failed', array $errors = [], int $code = 422)
    {
        $this->errors = $errors;
        parent::__construct($message, $code);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

<?php

namespace App\Exceptions\Domain;

class ValidationException extends \Exception
{
    protected $errors;

    public function __construct(string $message = 'Validation failed', $errors = null, $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

<?php

namespace App\Exceptions\Domain;

use Exception;

class EntityNotFoundException extends Exception
{
    public function __construct(string $message = 'Resource not found', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}

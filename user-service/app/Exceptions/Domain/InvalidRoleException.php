<?php

namespace App\Exceptions\Domain;

use Exception;

class InvalidRoleException extends Exception
{
    public function __construct(string $message = 'Role tidak valid', int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

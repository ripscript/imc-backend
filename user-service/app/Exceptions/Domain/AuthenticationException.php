<?php

namespace App\Exceptions\Domain;

use Exception;

class AuthenticationException extends Exception
{
    public function __construct(string $message = 'Email/username atau password salah', int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

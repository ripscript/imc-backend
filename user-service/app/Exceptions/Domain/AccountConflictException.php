<?php

namespace App\Exceptions\Domain;

use Exception;

class AccountConflictException extends Exception
{
    public function __construct(string $message = 'Email sudah terdaftar untuk akun internal', int $code = 409, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

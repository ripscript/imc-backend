<?php

namespace App\Exceptions\Domain;

use Exception;

class AccountBlockedException extends Exception
{
    public function __construct(string $message = 'Akun Anda telah diblokir, silakan hubungi admin', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

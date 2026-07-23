<?php

namespace App\Exceptions\Domain;

use Exception;

class OtpException extends Exception
{
    public function __construct(string $message = 'OTP tidak valid', int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

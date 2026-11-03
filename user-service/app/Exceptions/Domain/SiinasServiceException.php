<?php

namespace App\Exceptions\Domain;

use Exception;

class SiinasServiceException extends Exception
{
    public function __construct(string $message = 'Layanan SIINas tidak dapat dihubungi', int $code = 502, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

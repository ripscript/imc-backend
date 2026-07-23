<?php

namespace App\Exceptions\Domain;

use Exception;

class RateLimitException extends Exception
{
    public function __construct(string $message = 'Terlalu banyak permintaan. Silakan coba kembali nanti.', int $code = 429, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

<?php

namespace App\Exceptions\Domain;

class AuthenticationException extends \Exception
{
    public function __construct(string $message = 'Unauthenticated', $code = 401)
    {
        parent::__construct($message, $code);
    }
}

<?php

namespace App\Exceptions\Domain;

class InvalidRoleException extends \Exception
{
    public function __construct(string $message = 'Invalid role', $code = 422)
    {
        parent::__construct($message, $code);
    }
}

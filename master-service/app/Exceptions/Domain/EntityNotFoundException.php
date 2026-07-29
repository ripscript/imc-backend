<?php

namespace App\Exceptions\Domain;

class EntityNotFoundException extends \Exception
{
    public function __construct(string $entity = 'Resource', $code = 404)
    {
        parent::__construct("{$entity} not found", $code);
    }
}

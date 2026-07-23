<?php

namespace App\Exceptions\Domain;

use Exception;

class EntityNotFoundException extends Exception
{
    protected string $entityName;

    protected ?string $identifier;

    public function __construct(
        string $entityName,
        ?string $identifier = null,
        int $code = 404,
        ?\Throwable $previous = null
    ) {
        $this->entityName = $entityName;
        $this->identifier = $identifier;
        $message = sprintf('%s tidak ditemukan', $entityName);
        if ($identifier) {
            $message .= sprintf(': %s', $identifier);
        }
        parent::__construct($message, $code, $previous);
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }
}

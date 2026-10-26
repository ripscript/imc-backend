<?php

namespace App\Services\DTOs;

/**
 * DTO for partial updates. Tracks which keys were present in the input so
 * services can distinguish "absent" (keep current value) from explicit
 * null (clear the value).
 */
abstract class PartialDTO extends BaseDTO
{
    public array $provided = [];

    public function __construct(array $data)
    {
        $this->provided = array_keys($data);
        parent::__construct($data);
    }

    public function isProvided(string $key): bool
    {
        return in_array($key, $this->provided, true);
    }
}

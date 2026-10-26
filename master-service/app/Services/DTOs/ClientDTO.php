<?php

namespace App\Services\DTOs;

class ClientDTO extends BaseDTO
{
    public ?string $name = null;

    public ?string $identifier = null;

    public ?string $status = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

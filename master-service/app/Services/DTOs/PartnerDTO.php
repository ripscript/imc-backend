<?php

namespace App\Services\DTOs;

class PartnerDTO extends BaseDTO
{
    public ?string $name = null;

    public ?string $identifier = null;

    public ?string $status = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

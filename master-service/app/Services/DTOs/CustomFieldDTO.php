<?php

namespace App\Services\DTOs;

class CustomFieldDTO extends BaseDTO
{
    public ?string $code = null;

    public ?string $name = null;

    public ?string $entity_type = null;

    public ?string $field_type = null;

    public ?bool $is_required = null;

    public ?string $status = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

<?php

namespace App\Services\DTOs;

class CustomFieldValueDTO extends BaseDTO
{
    public ?string $entity_type = null;

    public ?int $entity_id = null;

    public ?array $values = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

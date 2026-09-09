<?php

namespace App\Services\DTOs;

class DocumentTypeDTO extends BaseDTO
{
    public ?string $code = null;

    public ?string $name = null;

    public ?string $allowed_extension = null;

    public ?int $max_size_mb = null;

    public ?string $status = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

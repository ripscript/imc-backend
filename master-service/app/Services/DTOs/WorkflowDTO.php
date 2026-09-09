<?php

namespace App\Services\DTOs;

class WorkflowDTO extends BaseDTO
{
    public ?string $code = null;

    public ?string $name = null;

    public ?int $version = null;

    public ?string $status = null;

    public ?array $stages = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

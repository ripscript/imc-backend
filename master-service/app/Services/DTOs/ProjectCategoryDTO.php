<?php

namespace App\Services\DTOs;

class ProjectCategoryDTO extends BaseDTO
{
    public ?string $code = null;

    public ?string $name = null;

    public ?string $status = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

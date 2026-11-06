<?php

namespace App\Services\DTOs;

class RoleDTO extends BaseDTO
{
    public ?string $name = null;

    public ?string $code = null;

    public ?bool $is_active = true;

    public ?bool $is_system = false;

    public ?array $permission_ids = [];

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

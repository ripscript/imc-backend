<?php

namespace App\Services\DTOs;

class PermissionDTO extends BaseDTO
{
    public ?string $name = null;

    public ?string $action = null;

    public ?string $code = null;

    public ?string $label = null;

    public ?string $group = null;

    public ?bool $is_system = false;
}

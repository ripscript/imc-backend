<?php

namespace App\Services\DTOs;

class WorkflowStageDTO extends BaseDTO
{
    public ?string $code = null;

    public ?string $name = null;

    public ?int $sequence = null;

    public ?bool $is_required = null;

    public ?bool $allow_skip = null;

    public ?bool $allow_repeat = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

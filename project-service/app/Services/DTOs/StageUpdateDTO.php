<?php

namespace App\Services\DTOs;

class StageUpdateDTO extends PartialDTO
{
    public ?string $name = null;

    public ?string $description = null;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public ?string $status = null;

    public ?int $pic_user_id = null;

    public ?bool $allow_skip = null;

    public ?bool $allow_repeat = null;

    public ?bool $requires_approval = null;

    public $depends_on_project_stage_id = null;

    public ?array $tasks = null;

    public ?array $deliverables = null;

    public ?string $updated_by = null;
}

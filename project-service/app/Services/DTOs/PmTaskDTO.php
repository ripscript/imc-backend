<?php

namespace App\Services\DTOs;

class PmTaskDTO extends PartialDTO
{
    public ?string $title = null;

    public ?string $description = null;

    public ?string $status = null;

    public ?string $priority = null;

    public ?int $assignee_id = null;

    public ?string $due_date = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

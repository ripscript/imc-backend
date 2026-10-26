<?php

namespace App\Services\DTOs;

class PmDeliverableDTO extends PartialDTO
{
    public ?string $name = null;

    public ?int $document_type_id = null;

    public ?bool $is_required = null;

    public ?string $status = null;

    public ?string $due_date = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;
}

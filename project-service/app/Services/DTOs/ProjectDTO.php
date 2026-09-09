<?php

namespace App\Services\DTOs;

class ProjectDTO extends BaseDTO
{
    public ?string $name = null;
    public ?string $description = null;
    public ?int $client_id = null;
    public ?int $partner_id = null;
    public ?int $category_id = null;
    public ?int $workflow_id = null;
    public ?int $pic_user_id = null;
    public ?string $start_date = null;
    public ?string $target_end_date = null;
    public ?string $status = null;
    public ?int $progress = null;
    public ?string $created_by = null;
    public ?string $updated_by = null;
    public array $attachments = [];
    public array $deleted_attachment_ids = [];
}

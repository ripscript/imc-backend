<?php

namespace App\Services\DTOs;

class AgendaDTO extends BaseDTO
{
    public int $content_id;

    public string $event_name;

    public ?bool $is_range;

    public ?bool $is_active;

    public ?string $datetime_start;

    public ?string $datetime_end;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->content_id = $data['content_id'] ?? 0;
        $this->event_name = $data['event_name'] ?? '';
        $this->is_range = $data['is_range'] ?? null;
        $this->is_active = $data['is_active'] ?? null;
        $this->datetime_start = $data['datetime_start'] ?? null;
        $this->datetime_end = $data['datetime_end'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

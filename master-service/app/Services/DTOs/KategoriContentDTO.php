<?php

namespace App\Services\DTOs;

class KategoriContentDTO extends BaseDTO
{
    public string $title;

    public ?bool $is_active;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->title = $data['title'] ?? '';
        $this->is_active = $data['is_active'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

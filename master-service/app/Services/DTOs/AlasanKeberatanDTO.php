<?php

namespace App\Services\DTOs;

class AlasanKeberatanDTO extends BaseDTO
{
    public string $name;

    public bool $is_active;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? '';
        $this->is_active = $data['is_active'] ?? false;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

<?php

namespace App\Services\DTOs;

class SatuanKerjaDTO extends BaseDTO
{
    public string $name;

    public ?array $sub;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? '';
        $this->sub = $data['sub'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

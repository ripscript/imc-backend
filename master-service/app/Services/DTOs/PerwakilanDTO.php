<?php

namespace App\Services\DTOs;

class PerwakilanDTO extends BaseDTO
{
    public string $name;

    public string $type;

    public ?array $negara_id;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? '';
        $this->type = $data['type'] ?? '';
        $this->negara_id = $data['negara_id'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

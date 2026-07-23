<?php

namespace App\Services\DTOs;

class SifatInformasiDTO extends BaseDTO
{
    public string $name;

    public ?string $icon_path;

    public ?string $description;

    public ?string $link;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? '';
        $this->icon_path = $data['icon_path'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->link = $data['link'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

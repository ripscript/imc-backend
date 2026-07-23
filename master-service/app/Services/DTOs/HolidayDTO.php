<?php

namespace App\Services\DTOs;

class HolidayDTO extends BaseDTO
{
    public string $name;

    public string $date;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? '';
        $this->date = $data['date'] ?? '';
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

<?php

namespace App\Services\DTOs;

class DashboardDTO extends BaseDTO
{
    public ?string $start_date;

    public ?string $end_date;

    public ?string $year;

    public function __construct(array $data = [])
    {
        $this->start_date = $data['start_date'] ?? null;
        $this->end_date = $data['end_date'] ?? null;
        $this->year = $data['year'] ?? null;
    }
}

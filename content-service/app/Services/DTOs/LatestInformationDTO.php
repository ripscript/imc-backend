<?php

namespace App\Services\DTOs;

class LatestInformationDTO extends BaseDTO
{
    public string $title;

    public ?string $url;

    public ?bool $is_active;

    public ?string $publish_date;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->title = $data['title'] ?? '';
        $this->url = $data['url'] ?? null;
        $this->is_active = $data['is_active'] ?? null;
        $this->publish_date = $data['publish_date'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

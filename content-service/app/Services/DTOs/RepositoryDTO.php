<?php

namespace App\Services\DTOs;

class RepositoryDTO extends BaseDTO
{
    public int $content_id;

    public string $title;

    public string $slug;

    public ?string $file_path;

    public ?string $thumbnail_path;

    public ?string $description;

    public ?string $publish_date;

    public ?bool $is_publicated;

    public ?array $tag_id;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->content_id = $data['content_id'] ?? 0;
        $this->title = $data['title'] ?? '';
        $this->slug = $data['slug'] ?? '';
        $this->file_path = $data['file_path'] ?? null;
        $this->thumbnail_path = $data['thumbnail_path'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->publish_date = $data['publish_date'] ?? null;
        $this->is_publicated = $data['is_publicated'] ?? null;
        $this->tag_id = $data['tag_id'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

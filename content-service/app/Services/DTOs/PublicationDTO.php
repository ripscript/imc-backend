<?php

namespace App\Services\DTOs;

class PublicationDTO extends BaseDTO
{
    public int $content_id;

    public string $title;

    public string $slug;

    public ?string $headline;

    public ?string $description;

    public ?string $publish_date;

    public ?bool $is_publicated;

    public ?bool $is_slider;

    public ?string $thumbnail_path;

    public ?int $kategori_content_id;

    public ?array $tag_id;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->content_id = $data['content_id'] ?? 0;
        $this->title = $data['title'] ?? '';
        $this->slug = $data['slug'] ?? '';
        $this->headline = $data['headline'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->publish_date = $data['publish_date'] ?? null;
        $this->is_publicated = $data['is_publicated'] ?? null;
        $this->is_slider = $data['is_slider'] ?? null;
        $this->thumbnail_path = $data['thumbnail_path'] ?? null;
        $this->kategori_content_id = $data['kategori_content_id'] ?? null;
        $this->tag_id = $data['tag_id'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

<?php

namespace App\Services\DTOs;

class ShowcaseDTO extends BaseDTO
{
    public int $content_id;

    public string $title;

    public string $slug;

    public ?string $description;

    public ?int $kategori_content_id;

    public ?string $thumbnail_path;

    public ?bool $is_publicated;

    public ?string $publish_date;

    public ?array $tag_id;

    public ?bool $has_additional_image;

    public ?bool $has_additional_video;

    public ?bool $has_additional_file;

    public ?bool $has_additional_link;

    public ?bool $has_additional_text;

    public ?array $content_additional_image;

    public ?array $content_additional_video;

    public ?array $content_additional_file;

    public ?array $content_additional_link;

    public $content_additional_text;

    public ?array $deleted_content_additional_image_id;

    public ?array $deleted_content_additional_video_id;

    public ?array $deleted_content_additional_file_id;

    public ?array $deleted_content_additional_link_id;

    public ?array $deleted_content_additional_text_id;

    public ?string $duration;

    public ?string $location;

    public ?string $start_date;

    public ?int $budget;

    public ?string $organization_address;

    public ?string $organization_name;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->content_id = $data['content_id'] ?? 0;
        $this->title = $data['title'] ?? '';
        $this->slug = $data['slug'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->kategori_content_id = $data['kategori_content_id'] ?? null;
        $this->thumbnail_path = $data['thumbnail_path'] ?? null;
        $this->is_publicated = $data['is_publicated'] ?? null;
        $this->publish_date = $data['publish_date'] ?? null;
        $this->tag_id = $data['tag_id'] ?? null;
        $this->has_additional_image = $data['has_additional_image'] ?? null;
        $this->has_additional_video = $data['has_additional_video'] ?? null;
        $this->has_additional_file = $data['has_additional_file'] ?? null;
        $this->has_additional_link = $data['has_additional_link'] ?? null;
        $this->has_additional_text = $data['has_additional_text'] ?? null;
        $this->content_additional_image = $data['content_additional_image'] ?? null;
        $this->content_additional_video = $data['content_additional_video'] ?? null;
        $this->content_additional_file = $data['content_additional_file'] ?? null;
        $this->content_additional_link = $data['content_additional_link'] ?? null;
        $this->content_additional_text = $data['content_additional_text'] ?? null;
        $this->deleted_content_additional_image_id = $data['deleted_content_additional_image_id'] ?? null;
        $this->deleted_content_additional_video_id = $data['deleted_content_additional_video_id'] ?? null;
        $this->deleted_content_additional_file_id = $data['deleted_content_additional_file_id'] ?? null;
        $this->deleted_content_additional_link_id = $data['deleted_content_additional_link_id'] ?? null;
        $this->deleted_content_additional_text_id = $data['deleted_content_additional_text_id'] ?? null;
        $this->duration = $data['duration'] ?? null;
        $this->location = $data['location'] ?? null;
        $this->start_date = $data['start_date'] ?? null;
        $this->budget = $data['budget'] ?? null;
        $this->organization_address = $data['organization_address'] ?? null;
        $this->organization_name = $data['organization_name'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

<?php

namespace App\Services\DTOs;

class MenuDTO extends BaseDTO
{
    public string $title;

    public ?string $description;

    public string $slug;

    public ?bool $is_active;

    public ?int $order;

    public ?bool $has_sub;

    public ?bool $is_show_front;

    public ?bool $is_show_side_menu;

    public ?string $type;

    public mixed $content;

    public ?string $sub_type;

    public ?int $layout_id;

    public ?bool $is_accessible;

    public ?bool $is_show_home;

    public ?bool $is_show_tag;

    public ?array $deleted_content_detail_id;

    public ?array $deleted_content_detail_list_id;

    public ?bool $has_additional_image;

    public ?bool $has_additional_video;

    public ?bool $has_additional_file;

    public ?bool $has_additional_link;

    public ?bool $has_additional_text;

    public mixed $content_additional_image;

    public mixed $content_additional_video;

    public mixed $content_additional_file;

    public mixed $content_additional_link;

    public mixed $content_additional_text;

    public ?array $deleted_content_additional_image_id;

    public ?array $deleted_content_additional_video_id;

    public ?array $deleted_content_additional_file_id;

    public ?array $deleted_content_additional_link_id;

    public ?array $deleted_content_additional_text_id;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->slug = $data['slug'] ?? '';
        $this->is_active = $data['is_active'] ?? null;
        $this->order = $data['order'] ?? null;
        $this->has_sub = $data['has_sub'] ?? null;
        $this->is_show_front = $data['is_show_front'] ?? null;
        $this->is_show_side_menu = $data['is_show_side_menu'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->content = $data['content'] ?? null;
        $this->sub_type = $data['sub_type'] ?? null;
        $this->layout_id = $data['layout_id'] ?? null;
        $this->is_accessible = $data['is_accessible'] ?? null;
        $this->is_show_home = $data['is_show_home'] ?? null;
        $this->is_show_tag = $data['is_show_tag'] ?? null;
        $this->deleted_content_detail_id = $data['deleted_content_detail_id'] ?? null;
        $this->deleted_content_detail_list_id = $data['deleted_content_detail_list_id'] ?? null;
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
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

<?php

namespace App\Services\DTOs;

class ContentDTO extends BaseDTO
{
    public string $parent_type;

    public ?int $parent_id;

    public ?string $section_title;

    public ?bool $is_section;

    public ?int $order;

    public ?string $type;

    public ?int $layout_id;

    public ?bool $is_show_home;

    public ?bool $is_show_tag;

    public ?string $sub_type;

    public ?bool $is_accessible;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->parent_type = $data['parent_type'] ?? '';
        $this->parent_id = $data['parent_id'] ?? null;
        $this->section_title = $data['section_title'] ?? null;
        $this->is_section = $data['is_section'] ?? null;
        $this->order = $data['order'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->layout_id = $data['layout_id'] ?? null;
        $this->is_show_home = $data['is_show_home'] ?? null;
        $this->is_show_tag = $data['is_show_tag'] ?? null;
        $this->sub_type = $data['sub_type'] ?? null;
        $this->is_accessible = $data['is_accessible'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

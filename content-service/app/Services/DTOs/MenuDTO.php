<?php

namespace App\Services\DTOs;

class MenuDTO extends BaseDTO
{
    public string $title;

    public string $slug;

    public ?bool $is_active;

    public ?int $order;

    public ?bool $has_sub;

    public ?bool $is_show_front;

    public ?bool $is_show_side_menu;

    public ?string $type;

    public ?int $layout_id;

    public ?bool $is_accessible;

    public ?bool $is_show_home;

    public ?bool $is_show_tag;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->title = $data['title'] ?? '';
        $this->slug = $data['slug'] ?? '';
        $this->is_active = $data['is_active'] ?? null;
        $this->order = $data['order'] ?? null;
        $this->has_sub = $data['has_sub'] ?? null;
        $this->is_show_front = $data['is_show_front'] ?? null;
        $this->is_show_side_menu = $data['is_show_side_menu'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->layout_id = $data['layout_id'] ?? null;
        $this->is_accessible = $data['is_accessible'] ?? null;
        $this->is_show_home = $data['is_show_home'] ?? null;
        $this->is_show_tag = $data['is_show_tag'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

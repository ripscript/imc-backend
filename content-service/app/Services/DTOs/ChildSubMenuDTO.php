<?php

namespace App\Services\DTOs;

class ChildSubMenuDTO extends BaseDTO
{
    public int $sub_menu_id;

    public string $title;

    public string $slug;

    public ?bool $is_active;

    public ?int $order;

    public ?string $type;

    public ?int $layout_id;

    public ?bool $is_accessible;

    public ?bool $is_show_home;

    public ?bool $is_show_tag;

    public ?string $created_by;

    public ?string $updated_by;

    public function __construct(array $data = [])
    {
        $this->sub_menu_id = $data['sub_menu_id'] ?? 0;
        $this->title = $data['title'] ?? '';
        $this->slug = $data['slug'] ?? '';
        $this->is_active = $data['is_active'] ?? null;
        $this->order = $data['order'] ?? null;
        $this->type = $data['type'] ?? null;
        $this->layout_id = $data['layout_id'] ?? null;
        $this->is_accessible = $data['is_accessible'] ?? null;
        $this->is_show_home = $data['is_show_home'] ?? null;
        $this->is_show_tag = $data['is_show_tag'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }
}

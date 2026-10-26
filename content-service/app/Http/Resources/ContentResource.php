<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'parent_type' => $this->parent_type,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'layout_id' => $this->layout_id,
            'section_title' => $this->section_title,
            'is_section' => $this->is_section,
            'is_show_home' => $this->is_show_home,
            'is_show_tag' => $this->is_show_tag,
            'sub_type' => $this->sub_type,
            'is_accessible' => $this->is_accessible,
            'order' => $this->order,
            'parent' => $this->whenLoaded('parent'),
            'content_detail' => $this->when($this->content, fn() => $this->content),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

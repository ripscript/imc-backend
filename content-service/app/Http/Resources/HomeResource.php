<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'parent_type' => $this->parent_type,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'section_title' => $this->section_title,
            'is_section' => $this->is_section,
            'order' => $this->order,
            'content' => $this->when($this->content, fn() => $this->content),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

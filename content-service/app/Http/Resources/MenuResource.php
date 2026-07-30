<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'order' => $this->order,
            'has_sub' => $this->has_sub,
            'is_show_front' => $this->is_show_front,
            'is_show_side_menu' => $this->is_show_side_menu,
            'is_dedicated' => $this->is_dedicated,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'content' => $this->whenLoaded('content'),
            'subMenu' => $this->whenLoaded('subMenu'),
        ];
    }
}

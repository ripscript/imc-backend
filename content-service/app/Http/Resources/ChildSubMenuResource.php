<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChildSubMenuResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sub_menu_id' => $this->sub_menu_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'order' => $this->order,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'subMenu' => $this->whenLoaded('subMenu'),
            'content' => $this->whenLoaded('content'),
        ];
    }
}

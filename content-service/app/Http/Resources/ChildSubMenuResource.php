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
            'description' => $this->description,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'order' => $this->order,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'type' => $this->type,
            'layout_id' => $this->layout_id,
            'is_accessible' => $this->is_accessible,
            'is_show_home' => $this->is_show_home,
            'is_show_tag' => $this->is_show_tag,
            'sub_type' => $this->sub_type,
            'subMenu' => $this->whenLoaded('subMenu'),
            'content' => $this->content_detail,
            'has_additional_image' => $this->has_additional_image ?? false,
            'has_additional_video' => $this->has_additional_video ?? false,
            'has_additional_link' => $this->has_additional_link ?? false,
            'has_additional_file' => $this->has_additional_file ?? false,
            'has_additional_text' => $this->has_additional_text ?? false,
            'content_additional_image' => $this->content_additional_image ?? [],
            'content_additional_video' => $this->content_additional_video ?? [],
            'content_additional_link' => $this->content_additional_link ?? [],
            'content_additional_file' => $this->content_additional_file ?? [],
            'content_additional_text' => $this->content_additional_text ?? null,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentDetailRepositoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'content_tag' => $this->whenLoaded('contentTag'),
            'layout' => $this->whenLoaded('layout'),
            'parent_menu' => $this->when(isset($this->parent_menu), $this->parent_menu),
            'breadcrumb' => $this->when(isset($this->breadcrumb), $this->breadcrumb),
        ];
    }
}

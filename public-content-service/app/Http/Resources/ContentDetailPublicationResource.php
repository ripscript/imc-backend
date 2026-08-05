<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentDetailPublicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'kategori_title' => $this->kategori_title,
            'content_tag' => $this->whenLoaded('contentTag'),
            'layout' => $this->whenLoaded('layout'),
            'additional_image' => $this->whenLoaded('contentAdditionalImage'),
            'additional_video' => $this->whenLoaded('contentAdditionalVideo'),
            'additional_link' => $this->whenLoaded('contentAdditionalLink'),
            'additional_file' => $this->whenLoaded('contentAdditionalFile'),
            'additional_text' => $this->whenLoaded('contentAdditionalText'),
            'parent_menu' => $this->when(isset($this->parent_menu), $this->parent_menu),
            'breadcrumb' => $this->when(isset($this->breadcrumb), $this->breadcrumb),
        ];
    }
}

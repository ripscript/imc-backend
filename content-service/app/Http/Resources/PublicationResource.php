<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content_id' => $this->content_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'headline' => $this->headline,
            'description' => $this->description,
            'is_publicated' => $this->is_publicated,
            'thumbnail_path' => $this->thumbnail_path,
            'publish_date' => $this->publish_date,
            'is_slider' => $this->is_slider,
            'layout_id' => $this->layout_id,
            'kategori_content_id' => $this->kategori_content_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'contentAdditionalImage' => $this->whenLoaded('contentAdditionalImage'),
            'contentAdditionalVideo' => $this->whenLoaded('contentAdditionalVideo'),
            'contentAdditionalFile' => $this->whenLoaded('contentAdditionalFile'),
            'contentAdditionalLink' => $this->whenLoaded('contentAdditionalLink'),
            'contentAdditionalText' => $this->whenLoaded('contentAdditionalText'),
        ];
    }
}

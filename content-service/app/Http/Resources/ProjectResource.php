<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content_id' => $this->content_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'thumbnail_path' => $this->thumbnail_path,
            'is_publicated' => $this->is_publicated,
            'publish_date' => $this->publish_date,
            'duration' => $this->duration,
            'location' => $this->location,
            'start_date' => $this->start_date,
            'budget' => $this->budget,
            'organization_address' => $this->organization_address,
            'organization_name' => $this->organization_name,
            'has_additional_image' => $this->has_additional_image,
            'has_additional_video' => $this->has_additional_video,
            'has_additional_file' => $this->has_additional_file,
            'has_additional_link' => $this->has_additional_link,
            'has_additional_text' => $this->has_additional_text,
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

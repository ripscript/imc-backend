<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentDetailProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'description' => $this->description,
            'thumbnail_path' => $this->thumbnail_path,
            'publish_date' => $this->publish_date,
            'duration' => $this->duration,
            'location' => $this->location,
            'start_date' => $this->start_date,
            'budget' => $this->budget,
            'organization_name' => $this->organization_name,
            'organization_address' => $this->organization_address,
            'kategori_title' => $this->kategori_title,
            'project_no' => $this->project_no,
            'status' => $this->status,
            'progress' => $this->progress,
            'target_end_date' => $this->target_end_date,
            'client_name' => $this->when(isset($this->client_name), $this->client_name),
            'partner_name' => $this->when(isset($this->partner_name), $this->partner_name),
            'category_name' => $this->when(isset($this->category_name), $this->category_name),
            'layout' => $this->whenLoaded('layout'),
            'additional_image' => $this->whenLoaded('contentAdditionalImage'),
            'additional_text' => $this->whenLoaded('contentAdditionalText'),
            'additional_video' => $this->whenLoaded('contentAdditionalVideo'),
            'additional_link' => $this->whenLoaded('contentAdditionalLink'),
            'additional_file' => $this->whenLoaded('contentAdditionalFile'),
            'parent_menu' => $this->when(isset($this->parent_menu), $this->parent_menu),
            'breadcrumb' => $this->when(isset($this->breadcrumb), $this->breadcrumb),
        ];
    }
}

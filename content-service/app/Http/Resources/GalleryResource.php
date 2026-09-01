<?php

namespace App\Http\Resources;

use App\Http\Resources\ContentDetailGalleryListResource;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content_id' => $this->content_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_publicated' => $this->is_publicated,
            'publish_date' => $this->publish_date,
            'layout_id' => $this->layout_id,
            'tag_id' => $this->tag_id ?? [],
            'date' => $this->date,
            'order' => $this->order,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'content' => $this->whenLoaded('content'),
            'list' => ContentDetailGalleryListResource::collection($this->whenLoaded('list')),
        ];
    }
}

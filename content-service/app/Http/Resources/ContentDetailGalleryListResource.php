<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContentDetailGalleryListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content_detail_gallery_id' => $this->content_detail_gallery_id,
            'title' => $this->title,
            'description' => $this->description,
            'image_path' => $this->image_path,
            'video_type' => $this->video_type,
            'video_path' => $this->video_path,
            'video_url' => $this->video_url,
            'order' => $this->order,
        ];
    }
}

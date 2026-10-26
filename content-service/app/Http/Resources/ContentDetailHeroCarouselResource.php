<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentDetailHeroCarouselResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'image_path' => $this->image_path,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'button_text' => $this->button_text,
            'button_link' => $this->button_link,
            'order' => $this->order,
        ];
    }
}

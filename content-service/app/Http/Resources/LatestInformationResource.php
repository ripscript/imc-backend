<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LatestInformationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'date' => $this->date,
            'url' => $this->url,
            'is_active' => $this->is_active,
            'order' => $this->order,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

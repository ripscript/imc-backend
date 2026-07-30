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
            'description' => $this->description,
            'file_url' => $this->file_url,
            'date' => $this->date,
            'order' => $this->order,
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

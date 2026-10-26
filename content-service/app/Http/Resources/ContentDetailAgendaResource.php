<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContentDetailAgendaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content_id' => $this->content_id,
            'title' => $this->title,
            'date' => $this->date,
            'time' => $this->time,
            'location' => $this->location,
            'description' => $this->description,
            'order' => $this->order,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

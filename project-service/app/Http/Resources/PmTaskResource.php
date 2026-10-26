<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PmTaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'assignee' => $this->assignee ? ['id' => $this->assignee->id, 'name' => $this->assignee->name] : null,
            'due_date' => $this->due_date,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PmDeliverableResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'document_type_id' => $this->document_type_id,
            'is_required' => $this->is_required,
            'status' => $this->status,
            'due_date' => $this->due_date,
        ];
    }
}

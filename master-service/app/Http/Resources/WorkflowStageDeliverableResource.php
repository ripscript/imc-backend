<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowStageDeliverableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'document_type_id' => $this->document_type_id,
            'is_required' => $this->is_required,
            'sequence' => $this->sequence,
        ];
    }
}

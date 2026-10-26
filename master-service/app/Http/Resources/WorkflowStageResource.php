<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowStageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'sequence' => $this->sequence,
            'is_required' => $this->is_required,
            'allow_skip' => $this->allow_skip,
            'allow_repeat' => $this->allow_repeat,
            'description' => $this->description,
            'requires_approval' => $this->requires_approval,
            'tasks' => WorkflowStageTaskResource::collection($this->whenLoaded('tasks')),
            'deliverables' => WorkflowStageDeliverableResource::collection($this->whenLoaded('deliverables')),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

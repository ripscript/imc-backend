<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectStageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'workflow_stage_id' => $this->workflow_stage_id,
            'name' => $this->name,
            'description' => $this->description,
            'sequence' => $this->sequence,
            'status' => $this->status,
            'progress' => $this->calculateProgress(),
            'pic' => $this->pic ? ['id' => $this->pic->id, 'name' => $this->pic->name, 'email' => $this->pic->email] : null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'completed_at' => $this->completed_at,
            'depends_on_project_stage_id' => $this->depends_on_project_stage_id,
            'is_required' => $this->is_required,
            'allow_skip' => $this->allow_skip,
            'allow_repeat' => $this->allow_repeat,
            'requires_approval' => $this->requires_approval,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'approver' => $this->approver ? ['id' => $this->approver->id, 'name' => $this->approver->name] : null,
            'is_configured' => $this->is_configured,
        ];
    }
}

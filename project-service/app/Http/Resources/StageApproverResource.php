<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StageApproverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_stage_id' => $this->project_stage_id,
            'approver_id' => $this->user_id,
            'user_id' => $this->user_id,
            'sequence' => $this->sequence ?? null,
            'approver' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role' => $this->user->role ?? null,
            ] : null,
        ];
    }
}

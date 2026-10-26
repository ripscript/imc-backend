<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PmChangeRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'change_no' => $this->change_no,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'reason' => $this->reason,
            'impact_scope' => $this->impact_scope,
            'proposed_changes' => $this->proposed_changes ?? (object) [],
            'status' => $this->status,
            'notes' => $this->notes,
            'client' => $this->client ? ['id' => $this->client->id, 'name' => $this->client->name] : null,
            'stage' => $this->stage ? ['id' => $this->stage->id, 'name' => $this->stage->name] : null,
            'requester' => $this->requester ? ['id' => $this->requester->id, 'name' => $this->requester->name] : null,
            'decider' => $this->decider ? ['id' => $this->decider->id, 'name' => $this->decider->name] : null,
            'approved_at' => $this->approved_at,
            'applied_at' => $this->applied_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

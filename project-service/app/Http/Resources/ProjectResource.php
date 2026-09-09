<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'project_no' => $this->project_no,
            'name' => $this->name,
            'description' => $this->description,
            'client' => $this->client ? ['id' => $this->client->id, 'name' => $this->client->name] : null,
            'partner' => $this->partner ? ['id' => $this->partner->id, 'name' => $this->partner->name] : null,
            'category' => $this->category ? ['code' => $this->category->code, 'name' => $this->category->name] : null,
            'workflow' => $this->workflow
                ? ['id' => $this->workflow->id, 'code' => $this->workflow->code, 'name' => $this->workflow->name, 'version' => $this->workflow->version]
                : null,
            'status' => $this->status,
            'progress' => $this->progress,
            'current_stage' => null,
            'pic' => $this->pic ? ['id' => $this->pic->id, 'name' => $this->pic->name] : null,
            'start_date' => $this->start_date,
            'target_end_date' => $this->target_end_date,
            'attachments' => ProjectAttachmentResource::collection($this->whenLoaded('attachments')),
            'stages' => [],
            'milestones' => [],
            'open_change_requests' => [],
            'custom_fields' => [],
        ];
    }
}

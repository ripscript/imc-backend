<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'project_no' => $this->project_no,
            'name' => $this->title,
            'title' => $this->title,
            'slug' => $this->slug,
            'thumbnail_path' => $this->thumbnail_path,
            'is_publicated' => $this->is_publicated,
            'publish_date' => $this->publish_date,
            'client' => $this->client ? ['id' => $this->client->id, 'name' => $this->client->name] : null,
            'partner' => $this->partner ? ['id' => $this->partner->id, 'name' => $this->partner->name] : null,
            'category' => $this->category ? ['code' => $this->category->code, 'name' => $this->category->name] : null,
            'status' => $this->status,
            'progress' => $this->progress,
            'current_stage' => $this->getAttribute('current_stage'),
            'pic' => $this->pic ? ['id' => $this->pic->id, 'name' => $this->pic->name, 'email' => $this->pic->email] : null,
            'start_date' => $this->start_date,
            'target_end_date' => $this->target_end_date,
            'attachments' => ProjectAttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'content_id' => $this->content_id,
            'project_no' => $this->project_no,
            'name' => $this->title,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'thumbnail_path' => $this->thumbnail_path,
            'is_publicated' => $this->is_publicated,
            'publish_date' => $this->publish_date,
            'duration' => $this->duration,
            'location' => $this->location,
            'budget' => $this->budget,
            'organization_name' => $this->organization_name,
            'organization_address' => $this->organization_address,
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
            'has_additional_image' => $this->has_additional_image,
            'has_additional_video' => $this->has_additional_video,
            'has_additional_file' => $this->has_additional_file,
            'has_additional_link' => $this->has_additional_link,
            'has_additional_text' => $this->has_additional_text,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'tag_id' => $this->tag_id ?? [],
            'attachments' => ProjectAttachmentResource::collection($this->whenLoaded('attachments')),
            'contentAdditionalImage' => $this->whenLoaded('contentAdditionalImage'),
            'contentAdditionalVideo' => $this->whenLoaded('contentAdditionalVideo'),
            'contentAdditionalFile' => $this->whenLoaded('contentAdditionalFile'),
            'contentAdditionalLink' => $this->whenLoaded('contentAdditionalLink'),
            'contentAdditionalText' => $this->whenLoaded('contentAdditionalText'),
            'stages' => [],
            'milestones' => [],
            'open_change_requests' => [],
            'custom_fields' => [],
        ];
    }
}

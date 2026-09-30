<?php

namespace App\Http\Resources;

use App\Models\ProjectStage;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray($request)
    {
        $subject = $this->relationLoaded('subject') ? $this->subject : null;

        return [
            'id' => $this->id,
            'activity_type' => $this->event,
            'description' => $this->description,
            'user' => $this->causer ? ['id' => $this->causer->id, 'name' => $this->causer->name] : null,
            'project_stage_id' => $subject instanceof ProjectStage ? $subject->id : null,
            'created_at' => $this->created_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectStageDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return array_merge((new ProjectStageResource($this->resource))->toArray($request), [
            'tasks' => PmTaskResource::collection($this->whenLoaded('tasks')),
            'deliverables' => PmDeliverableResource::collection($this->whenLoaded('deliverables')),
            'documents' => PmDocumentResource::collection($this->whenLoaded('documents')),
        ]);
    }
}

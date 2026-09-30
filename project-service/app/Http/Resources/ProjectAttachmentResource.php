<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectAttachmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'file_url' => $this->file_url,
            'file_name' => $this->file_name,
            'file_size_kb' => $this->file_size_kb !== null ? (int) $this->file_size_kb : null,
            'created_by' => $this->created_by,
        ];
    }
}

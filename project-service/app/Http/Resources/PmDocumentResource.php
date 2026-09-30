<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PmDocumentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'document_type_id' => $this->document_type_id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'file_size_kb' => $this->file_size_kb !== null ? (int) $this->file_size_kb : null,
            'version' => $this->version,
            'status' => $this->status,
            'uploaded_by' => $this->uploader ? ['id' => $this->uploader->id, 'name' => $this->uploader->name] : null,
            'uploaded_at' => $this->uploaded_at,
        ];
    }
}

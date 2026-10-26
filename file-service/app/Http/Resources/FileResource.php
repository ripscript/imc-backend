<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'url' => $this->url,
            'path' => $this->path,
            'mime_type' => $this->mime_type,
            'filename' => $this->filename,
            'size' => $this->size,
        ];
    }
}

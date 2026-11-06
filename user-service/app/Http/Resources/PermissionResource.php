<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'action' => $this->action,
            'code' => $this->code,
            'label' => $this->label,
            'group' => $this->group,
            'is_system' => (bool) $this->is_system,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'is_active' => (bool) $this->is_active,
            'is_system' => (bool) $this->is_system,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'permissions_count' => $this->whenCounted('permissions'),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

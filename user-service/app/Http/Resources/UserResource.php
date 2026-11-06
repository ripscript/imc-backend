<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
            'name' => $this->name,
            'gender' => $this->gender,
            'phone_number' => $this->phone_number,
            'status' => $this->status,
            'role_id' => $this->role_id,
            'role' => $this->whenLoaded('role', fn () => $this->role->name),
            'permissions' => $this->getPermissionsList(),
            'satuan_kerja_id' => $this->satuan_kerja_id,
            'sub_satuan_kerja_id' => $this->sub_satuan_kerja_id,
            'perwakilan_id' => $this->perwakilan_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

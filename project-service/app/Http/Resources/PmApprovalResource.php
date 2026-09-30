<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PmApprovalResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'approval_id' => $this->id,
            'sequence' => $this->sequence,
            'approver' => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
                'email' => $this->approver->email,
            ] : null,
            'status' => $this->status,
        ];
    }
}

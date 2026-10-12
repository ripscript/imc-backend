<?php

namespace App\Http\Resources;

use App\Models\PmStageApprover;
use Illuminate\Http\Resources\Json\JsonResource;

class UnifiedApprovalResource extends JsonResource
{
    public function toArray($request): array
    {
        $caller = $request?->user('admin') ?? $request?->user() ?? auth('admin')->user();
        $canApprove = false;

        if ($caller && $this->isPending()) {
            $caller->loadMissing('role');
            $isCentral = $caller->role && in_array($caller->role->code, ['super_admin', 'admin_pusat'], true);

            if ($isCentral) {
                $canApprove = true;
            } elseif ($this->type === 'stage_clearance') {
                $canApprove = PmStageApprover::where('project_stage_id', $this->approvable_id)
                    ->where('user_id', $caller->id)
                    ->exists();
            }
        }

        $date = $this->requested_at ?? $this->created_at;
        $formattedDate = $date ? $date->copy()->locale('id')->translatedFormat('d F Y') : null;

        $typeLabel = match ($this->type) {
            'activation' => 'Aktivasi Proyek',
            'stage_clearance' => 'Stage Clearance',
            'change_request' => 'Change Request (CR)',
            default => ucfirst(str_replace('_', ' ', (string) $this->type)),
        };

        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'project_name' => $this->project?->title,
            'request_type' => $this->type,
            'request_type_label' => $typeLabel,
            'approvable_type' => $this->approvable_type,
            'approvable_id' => $this->approvable_id,
            'title' => $this->title,
            'requester_name' => $this->requester?->name,
            'requested_at' => $this->requested_at?->toISOString(),
            'formatted_date' => $formattedDate,
            'detail' => $this->description ?: $this->title,
            'status' => $this->status,
            'can_approve' => $canApprove,
            'rejection_notes' => $this->rejection_notes,
            'approved_by' => $this->approved_by,
            'approved_by_name' => $this->decider?->name,
            'approved_at' => $this->approved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

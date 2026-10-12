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
                if (isset($caller->assigned_stage_ids) && is_array($caller->assigned_stage_ids)) {
                    $canApprove = in_array((int) $this->approvable_id, $caller->assigned_stage_ids, true);
                } elseif ($this->relationLoaded('approvable') && $this->approvable && $this->approvable->relationLoaded('approvers')) {
                    $canApprove = $this->approvable->approvers->contains('user_id', $caller->id);
                } else {
                    $caller->assigned_stage_ids = PmStageApprover::where('user_id', $caller->id)
                        ->pluck('project_stage_id')
                        ->map(fn ($id) => (int) $id)
                        ->all();

                    $canApprove = in_array((int) $this->approvable_id, $caller->assigned_stage_ids, true);
                }
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

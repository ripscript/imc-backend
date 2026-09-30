<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmApproval;
use Illuminate\Support\Facades\DB;

class PmApprovalService
{
    use ResolvesProjectStage;

    public function list(int $projectId, int $stageId)
    {
        $stage = $this->resolveStage($projectId, $stageId);

        return PmApproval::with('approver')
            ->where('project_stage_id', $stage->id)
            ->orderBy('sequence')
            ->get();
    }

    /**
     * Bulk upsert of the stage's designated approvers (pre-created approval
     * rows, decision 3). Entries are ordered by `sequence` (or array order)
     * and stored as 1..n; removed approvers are soft-deleted.
     */
    public function sync(int $projectId, int $stageId, array $entries, ?string $actor = null)
    {
        return DB::transaction(function () use ($projectId, $stageId, $entries, $actor) {
            $stage = $this->resolveStage($projectId, $stageId);

            if (! $stage->requires_approval) {
                throw new ValidationException(
                    'Stage ini tidak membutuhkan approval.',
                    ['approvers' => ['Aktifkan "Perlu Approval" pada stage terlebih dahulu.']]
                );
            }

            $ordered = collect($entries)->sortBy(fn ($entry) => $entry['sequence'] ?? PHP_INT_MAX)->values();

            $existing = PmApproval::where('project_stage_id', $stage->id)->get()->keyBy('approver_id');

            $sequence = 1;
            $keptIds = [];
            foreach ($ordered as $entry) {
                $row = $existing->get($entry['approver_id']);

                if ($row) {
                    $row->sequence = $sequence++;
                    $row->updated_by = $actor;
                    $row->save();
                } else {
                    $row = PmApproval::create([
                        'project_stage_id' => $stage->id,
                        'sequence' => $sequence++,
                        'approver_id' => $entry['approver_id'],
                        'status' => 'pending',
                        'created_by' => $actor,
                        'updated_by' => $actor,
                    ]);
                }

                $keptIds[] = $row->id;
            }

            PmApproval::where('project_stage_id', $stage->id)
                ->whereNotIn('id', $keptIds)
                ->get()
                ->each(fn (PmApproval $approval) => $approval->delete());

            return PmApproval::with('approver')
                ->where('project_stage_id', $stage->id)
                ->orderBy('sequence')
                ->get();
        });
    }
}

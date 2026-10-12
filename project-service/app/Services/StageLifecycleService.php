<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmApprovalRequest;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Execution-time stage transitions (post-activation): gated complete, skip,
 * and repeat-reopen, plus auto-advance to the next stage and project
 * completion. See specs/005-project-approval-rework.
 *
 * `requires_approval` is an EXIT gate: a flagged stage may only be marked
 * complete by the fixed approver role (`super_admin`, `admin_pusat`);
 * unflagged stages complete directly. The clearance decision is recorded
 * on the stage (`approved_by`/`approved_at`).
 */
class StageLifecycleService
{
    use ResolvesProjectStage;

    /**
     * Fixed approver role set for stage clearance (specs/005 decision Q1).
     */
    public const APPROVER_ROLES = ['super_admin', 'admin_pusat'];

    /**
     * Mark an in-progress or pending_clearance stage completed once every gate is satisfied,
     * then advance to the next stage (or complete the project).
     */
    public function complete(int $projectId, int $stageId, User $actor): array
    {
        return DB::transaction(function () use ($projectId, $stageId, $actor) {
            $project = Project::findOrFail($projectId);
            $stage = $this->resolveStage($projectId, $stageId);

            $this->assertActive($project);

            if (! in_array($stage->status, ['in_progress', 'pending_clearance'], true)) {
                throw new ValidationException(
                    'Hanya stage yang sedang berjalan atau menunggu clearance yang dapat diselesaikan.',
                    ['stage' => ["Status stage saat ini: {$stage->status}."]]
                );
            }

            if ($stage->requires_approval) {
                $this->assertApprover($stage, $actor);
            }

            $missing = $this->completionBlockers($stage);

            if (! empty($missing)) {
                throw new ValidationException('Stage belum dapat diselesaikan.', $missing);
            }

            $stage->status = 'completed';
            $stage->completed_at = now();
            $stage->rejection_notes = null;

            if ($stage->requires_approval) {
                $stage->approved_by = $actor->id;
                $stage->approved_at = now();
            }

            $stage->updated_by = $actor->name;
            $stage->save();

            PmApprovalRequest::where('approvable_type', 'stage_clearance')
                ->where('approvable_id', $stage->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved',
                    'approved_by' => $actor->id,
                    'approved_at' => now(),
                ]);

            $next = $this->advance($project, $actor);

            return $this->payload($stage, $project, $next);
        });
    }

    /**
     * Submit an in-progress stage for clearance approval (specs/006).
     */
    public function requestClearance(int $projectId, int $stageId, User $actor, ?string $notes = null): array
    {
        return DB::transaction(function () use ($projectId, $stageId, $actor, $notes) {
            $project = Project::findOrFail($projectId);
            $stage = $this->resolveStage($projectId, $stageId);

            $this->assertActive($project);
            $this->assertInProgress($stage);

            if (! $stage->requires_approval) {
                throw new ValidationException(
                    'Stage ini tidak memerlukan approval clearance.',
                    ['stage' => ['Stage ini tidak dikonfigurasi untuk clearance approval. Selesaikan stage secara langsung.']]
                );
            }

            $missing = $this->completionBlockers($stage);

            if (! empty($missing)) {
                throw new ValidationException('Stage belum dapat diajukan clearance.', $missing);
            }

            $stage->status = 'pending_clearance';
            $stage->clearance_requested_by = $actor->id;
            $stage->clearance_requested_at = now();
            $stage->rejection_notes = null;
            $stage->updated_by = $actor->name;
            $stage->save();

            PmApprovalRequest::create([
                'project_id' => $project->id,
                'approvable_type' => 'stage_clearance',
                'approvable_id' => $stage->id,
                'type' => 'stage_clearance',
                'title' => "Stage Clearance: {$stage->name}",
                'description' => $notes,
                'status' => 'pending',
                'requested_by' => $actor->id,
                'requested_at' => now(),
            ]);

            return $this->payload($stage, $project, null);
        });
    }

    /**
     * Reject a clearance request, reverting the stage to in_progress with notes (specs/006).
     */
    public function rejectClearance(int $projectId, int $stageId, User $actor, string $notes): array
    {
        return DB::transaction(function () use ($projectId, $stageId, $actor, $notes) {
            $project = Project::findOrFail($projectId);
            $stage = $this->resolveStage($projectId, $stageId);

            $this->assertActive($project);

            if (! $this->canApproveStage($stage, $actor)) {
                throw new AccessDeniedHttpException('Anda tidak memiliki kewenangan untuk menolak clearance stage ini.');
            }

            if ($stage->status !== 'pending_clearance') {
                throw new ValidationException(
                    'Hanya stage dengan status pending clearance yang dapat ditolak.',
                    ['stage' => ["Status stage saat ini: {$stage->status}."]]
                );
            }

            if (empty(trim($notes))) {
                throw new ValidationException(
                    'Alasan penolakan wajib diisi.',
                    ['notes' => ['Alasan penolakan clearance wajib diisi.']]
                );
            }

            $stage->status = 'in_progress';
            $stage->rejection_notes = $notes;
            $stage->updated_by = $actor->name;
            $stage->save();

            PmApprovalRequest::where('approvable_type', 'stage_clearance')
                ->where('approvable_id', $stage->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'rejection_notes' => $notes,
                    'approved_by' => $actor->id,
                    'approved_at' => now(),
                ]);

            return $this->payload($stage, $project, null);
        });
    }

    /**
     * Skip an in-progress stage that allows skipping (bypasses all gates).
     */
    public function skip(int $projectId, int $stageId, User $actor): array
    {
        return DB::transaction(function () use ($projectId, $stageId, $actor) {
            $project = Project::findOrFail($projectId);
            $stage = $this->resolveStage($projectId, $stageId);

            $this->assertActive($project);
            $this->assertInProgress($stage);

            if (! $stage->allow_skip) {
                throw new ValidationException(
                    'Stage ini tidak dapat dilewati.',
                    ['stage' => ['Aktifkan "Allow Skip" untuk melewati stage ini.']]
                );
            }

            $stage->status = 'skipped';
            $stage->updated_by = $actor->name;
            $stage->save();

            $next = $this->advance($project, $actor);

            return $this->payload($stage, $project, $next);
        });
    }

    /**
     * Reopen a completed stage that allows repetition (allow_repeat), so the
     * team can run it again. Children are left untouched; a re-run needs a
     * fresh clearance when the stage requires approval.
     */
    public function reopen(int $projectId, int $stageId, User $actor): array
    {
        return DB::transaction(function () use ($projectId, $stageId, $actor) {
            $project = Project::findOrFail($projectId);
            $stage = $this->resolveStage($projectId, $stageId);

            $this->assertActive($project);

            if ($stage->status !== 'completed') {
                throw new ValidationException(
                    'Hanya stage yang sudah selesai dapat dibuka kembali.',
                    ['stage' => ["Status stage saat ini: {$stage->status}."]]
                );
            }

            if (! $stage->allow_repeat) {
                throw new ValidationException(
                    'Stage ini tidak dapat diulang.',
                    ['stage' => ['Aktifkan "Allow Repeat" untuk membuka kembali stage ini.']]
                );
            }

            $stage->status = 'in_progress';
            $stage->completed_at = null;
            $stage->approved_by = null;
            $stage->approved_at = null;
            $stage->updated_by = $actor->name;
            $stage->save();

            // A completed project re-enters execution when one of its stages
            // is reopened.
            if ($project->status === 'completed') {
                $project->status = 'active';
                $project->updated_by = $actor->name;
                $project->save();
            }

            return $this->payload($stage, $project, null);
        });
    }

    public static function isApprover(User $actor): bool
    {
        $actor->loadMissing('role');

        return $actor->role && in_array($actor->role->code, self::APPROVER_ROLES, true);
    }

    /**
     * Machine-readable blockers, keyed `stage_{id}.<gate>`.
     */
    private function completionBlockers(ProjectStage $stage): array
    {
        $errors = [];
        $sid = $stage->id;

        if ($stage->depends_on_project_stage_id) {
            $dependency = ProjectStage::find($stage->depends_on_project_stage_id);

            if ($dependency && ! in_array($dependency->status, ['completed', 'skipped'], true)) {
                $errors["stage_{$sid}.dependency"] = ["Stage '{$dependency->name}' belum selesai."];
            }
        }

        $openTasks = $stage->tasks()->whereNotIn('status', ['done', 'cancelled'])->get();

        if ($openTasks->isNotEmpty()) {
            $errors["stage_{$sid}.open_tasks"] = $openTasks
                ->map(fn ($task) => "Task '{$task->title}' belum selesai.")
                ->all();
        }

        $openDeliverables = $stage->deliverables()
            ->where('is_required', true)
            ->where('status', '!=', 'completed')
            ->get();

        if ($openDeliverables->isNotEmpty()) {
            $errors["stage_{$sid}.open_deliverables"] = $openDeliverables
                ->map(fn ($deliverable) => "Deliverable '{$deliverable->name}' belum selesai.")
                ->all();
        }

        return $errors;
    }

    /**
     * Move execution to the first stage that is neither completed nor
     * skipped: it becomes `in_progress`. When none remains, the project is
     * completed (a project counts as complete when ALL stages complete;
     * `requires_approval` only controls whether a stage needs clearance).
     */
    private function advance(Project $project, User $actor): ?ProjectStage
    {
        $next = $project->stages()
            ->whereNotIn('status', ['completed', 'skipped'])
            ->orderBy('sequence')
            ->first();

        if (! $next) {
            $project->status = 'completed';
            $project->updated_by = $actor->name;
            $project->save();

            return null;
        }

        if ($next->status === 'pending') {
            $next->status = 'in_progress';
            $next->updated_by = $actor->name;
            $next->save();
        }

        return $next;
    }

    private function assertActive(Project $project): void
    {
        if ($project->status !== 'active') {
            throw new ValidationException(
                'Transisi stage hanya berlaku pada proyek yang aktif.',
                ['project' => ["Status proyek saat ini: {$project->status}."]]
            );
        }
    }

    private function assertInProgress(ProjectStage $stage): void
    {
        if ($stage->status !== 'in_progress') {
            throw new ValidationException(
                'Hanya stage yang sedang berjalan dapat diubah.',
                ['stage' => ["Status stage saat ini: {$stage->status}."]]
            );
        }
    }

    public function canApproveStage(ProjectStage $stage, User $actor): bool
    {
        if (self::isApprover($actor)) {
            return true;
        }

        return $stage->approvers()->where('user_id', $actor->id)->exists();
    }

    private function assertApprover(ProjectStage $stage, User $actor): void
    {
        if (! $this->canApproveStage($stage, $actor)) {
            throw new AccessDeniedHttpException('Stage ini membutuhkan clearance dari approver yang ditugaskan atau admin pusat untuk diselesaikan.');
        }
    }

    /**
     * @return array{stage: ProjectStage, project_status: string, next_stage: ?ProjectStage}
     */
    private function payload(ProjectStage $stage, Project $project, ?ProjectStage $next): array
    {
        return [
            'stage' => $stage->fresh()->load(['pic', 'approver', 'approvers.user']),
            'project_status' => $project->fresh()->status,
            'next_stage' => $next ? $next->fresh()->load(['pic', 'approver', 'approvers.user']) : null,
        ];
    }
}

<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmApprovalRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Gate 1 — project activation (specs/005).
 *
 * Activation is ALWAYS two-step: `draft -> pending_approval -> active`.
 * The decision belongs to a fixed approver role
 * (`super_admin`, `admin_pusat` — enforced on the route); any stage
 * `requires_approval` flags are unrelated exit-gates (gate 2).
 */
class ProjectActivationService
{
    public function __construct(
        protected ProjectSetupStateService $setupStateService,
        protected ProjectService $projectService
    ) {}

    /**
     * Submit a complete draft project for activation approval.
     */
    public function activate(int $projectId, ?int $userId = null, ?string $userName = null): Project
    {
        $project = Project::findOrFail($projectId);

        if ($project->status !== 'draft') {
            throw new ConflictHttpException('Proyek tidak dapat diaktivasi pada status '.$project->status.'.');
        }

        $state = $this->setupStateService->state($projectId);

        if (! $state['ready_to_activate']) {
            throw new ValidationException('Proyek belum lengkap dan tidak dapat diaktivasi.', $state['missing']);
        }

        DB::transaction(function () use ($project, $userId, $userName) {
            $project->status = 'pending_approval';
            $project->activation_requested_by = $userId;
            $project->activation_requested_at = now();
            $project->rejection_notes = null;

            if (empty($project->start_date)) {
                $project->start_date = $project->stages()->min('start_date') ?? now()->toDateString();
            }
            $project->updated_by = $userName;
            $project->save();

            PmApprovalRequest::create([
                'project_id' => $project->id,
                'approvable_type' => 'project_activation',
                'approvable_id' => $project->id,
                'type' => 'activation',
                'title' => $project->title,
                'description' => null,
                'status' => 'pending',
                'requested_by' => $userId,
                'requested_at' => now(),
            ]);

            $picIds = collect([$project->pic_user_id])
                ->merge($project->stages()->pluck('pic_user_id'))
                ->filter()
                ->unique();

            foreach ($picIds as $picId) {
                (new PmProjectMemberService)->syncPic($project->id, (int) $picId, $userName);
            }
        });

        return $this->projectService->detail($project->id);
    }

    /**
     * Approve a pending activation: the project goes live and its first
     * stage starts.
     */
    public function approveActivation(int $projectId, User $actor): Project
    {
        $project = Project::findOrFail($projectId);

        if ($project->status !== 'pending_approval') {
            throw new ConflictHttpException('Aktivasi tidak dapat disetujui pada status '.$project->status.'.');
        }

        DB::transaction(function () use ($project, $actor) {
            $project->status = 'active';
            $project->rejection_notes = null;
            $project->activated_by = $actor->id;
            $project->activated_at = now();
            $project->updated_by = $actor->name;
            $project->save();

            PmApprovalRequest::where('approvable_type', 'project_activation')
                ->where('approvable_id', $project->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved',
                    'approved_by' => $actor->id,
                    'approved_at' => now(),
                ]);

            $first = $project->stages()->orderBy('sequence')->first();

            if ($first && $first->status === 'pending') {
                $first->status = 'in_progress';
                $first->updated_by = $actor->name;
                $first->save();
            }
        });

        return $this->projectService->detail($project->id);
    }

    /**
     * Reject a pending activation: back to draft for rework + re-activation.
     */
    public function rejectActivation(int $projectId, User $actor, string $notes): Project
    {
        $project = Project::findOrFail($projectId);

        if ($project->status !== 'pending_approval') {
            throw new ConflictHttpException('Aktivasi tidak dapat ditolak pada status '.$project->status.'.');
        }

        if (empty(trim($notes))) {
            throw new ValidationException(
                'Alasan penolakan wajib diisi.',
                ['notes' => ['Alasan penolakan aktivasi wajib diisi.']]
            );
        }

        DB::transaction(function () use ($project, $actor, $notes) {
            $project->status = 'draft';
            $project->rejection_notes = $notes;
            $project->updated_by = $actor->name;
            $project->save();

            PmApprovalRequest::where('approvable_type', 'project_activation')
                ->where('approvable_id', $project->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'rejection_notes' => $notes,
                    'approved_by' => $actor->id,
                    'approved_at' => now(),
                ]);
        });

        return $this->projectService->detail($project->id);
    }
}

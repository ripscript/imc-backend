<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmApproval;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProjectActivationService
{
    public function __construct(
        protected ProjectSetupStateService $setupStateService,
        protected ProjectService $projectService
    ) {}

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
            $project->status = 'active';

            if (empty($project->start_date)) {
                $project->start_date = $project->stages()->min('start_date') ?? now()->toDateString();
            }
            $project->updated_by = $userName;
            $project->save();

            $first = $project->stages()->orderBy('sequence')->first();

            if ($first && $first->status === 'pending') {
                $first->status = 'in_progress';
                $first->updated_by = $userName;
                $first->save();
            }

            if ($first) {
                PmApproval::where('project_stage_id', $first->id)
                    ->whereNull('requested_at')
                    ->get()
                    ->each(function (PmApproval $approval) use ($userId, $userName) {
                        $approval->requested_at = now();
                        $approval->requested_by = $userId;
                        $approval->updated_by = $userName;
                        $approval->save();
                    });
            }

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
}

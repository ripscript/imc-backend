<?php

namespace App\Services;

use App\Models\PmDeliverable;
use App\Models\Project;

/**
 * Review & Activate summary (S8). Aggregates are derived; the endpoint
 * always returns 200 — incompleteness is reported via `setup`.
 */
class ProjectReviewService
{
    public function __construct(
        protected ProjectSetupStateService $setupStateService
    ) {}

    public function review(int $projectId): array
    {
        $project = Project::with(['client', 'pic', 'workflow', 'stages'])
            ->findOrFail($projectId);

        $stages = $project->stages->sortBy('sequence')->values();
        $stageIds = $stages->pluck('id');

        // Distinct PICs from the project + its stages (source of truth before
        // activation syncs them into pm_project_members).
        $picIds = collect([$project->pic_user_id])
            ->merge($stages->pluck('pic_user_id'))
            ->filter()
            ->unique()
            ->values();

        return [
            'summary' => [
                'project_no' => $project->project_no,
                'name' => $project->title,
                'client' => $project->client ? ['id' => $project->client->id, 'name' => $project->client->name] : null,
                'pic' => $project->pic ? ['id' => $project->pic->id, 'name' => $project->pic->name] : null,
                'workflow' => $project->workflow ? [
                    'id' => $project->workflow->id,
                    'name' => $project->workflow->name,
                    'version' => $project->workflow->version,
                ] : null,
                'total_stage' => $stages->count(),
                'period' => [
                    'start_date' => $project->start_date?->toDateString(),
                    'target_end_date' => $project->target_end_date?->toDateString(),
                ],
                'total_duration_days' => $project->start_date && $project->target_end_date
                    ? ProjectTimelineService::durationDays($project->start_date, $project->target_end_date)
                    : null,
                'dokumen_wajib' => $stageIds->isEmpty() ? 0 : PmDeliverable::whereIn('project_stage_id', $stageIds)
                    ->where('is_required', true)
                    ->count(),
                'total_pic' => $picIds->count(),
                // Stages that will need clearance (exit-gate) before completion.
                'total_approval' => $stages->where('requires_approval', true)->count(),
            ],
            'timeline_preview' => $stages->map(fn ($stage) => [
                'no' => $stage->sequence,
                'name' => $stage->name,
                'start_date' => $stage->start_date?->toDateString(),
                'end_date' => $stage->end_date?->toDateString(),
            ])->all(),
            'setup' => $this->setupStateService->state($project->id),
        ];
    }
}

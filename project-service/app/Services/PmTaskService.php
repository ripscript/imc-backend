<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmTask;
use App\Models\ProjectStage;
use App\Services\DTOs\PmTaskDTO;
use App\Services\DTOs\QueryFilterDTO;

class PmTaskService
{
    use ResolvesProjectStage;

    public function __construct(
        protected QueryService $queryService
    ) {}

    public function list(int $projectId, int $stageId, QueryFilterDTO $filter)
    {
        $stage = $this->resolveStage($projectId, $stageId);

        return $this->queryService->applyFiltersAndPaginate(
            PmTask::with('assignee')->where('project_stage_id', $stage->id)->orderBy('id'),
            $filter,
            ['title'],
            'id'
        );
    }

    public function create(int $projectId, int $stageId, PmTaskDTO $dto): PmTask
    {
        $stage = $this->resolveStage($projectId, $stageId);

        return PmTask::create([
            'project_stage_id' => $stage->id,
            'title' => $dto->title,
            'description' => $dto->description,
            'status' => $dto->status ?? 'pending',
            'priority' => $dto->priority ?? 'medium',
            'assignee_id' => $dto->assignee_id,
            'due_date' => $dto->due_date,
            'created_by' => $dto->created_by,
            'updated_by' => $dto->updated_by,
        ])->load('assignee');
    }

    public function update(int $projectId, int $stageId, int $taskId, PmTaskDTO $dto): PmTask
    {
        $task = $this->resolveTask($projectId, $stageId, $taskId);

        foreach (['title', 'description', 'status', 'priority', 'assignee_id', 'due_date'] as $field) {
            if ($dto->isProvided($field)) {
                $task->{$field} = $dto->{$field};
            }
        }
        $task->updated_by = $dto->updated_by;
        $task->save();

        return $task->load('assignee');
    }

    public function delete(int $projectId, int $stageId, int $taskId): void
    {
        $this->resolveTask($projectId, $stageId, $taskId)->delete();
    }

    /**
     * Flip done <-> pending. Returns counters driving the "x/y Selesai" badge.
     */
    public function toggle(int $projectId, int $taskId, ?string $actor = null): array
    {
        $stageIds = ProjectStage::where('project_id', $projectId)->pluck('id');

        $task = PmTask::where('id', $taskId)->whereIn('project_stage_id', $stageIds)->first();

        if (! $task) {
            throw new ValidationException(
                'Task tidak ditemukan pada proyek ini.',
                ['task' => ["Task id {$taskId} bukan bagian dari proyek {$projectId}."]]
            );
        }

        $task->status = $task->status === 'done' ? 'pending' : 'done';
        $task->updated_by = $actor;
        $task->save();

        $tasks = PmTask::where('project_stage_id', $task->project_stage_id)->get();

        return [
            'status' => $task->status,
            'stage_progress' => $task->stage()->first()->calculateProgress(),
            'done_count' => $tasks->where('status', 'done')->count(),
            'total_count' => $tasks->count(),
        ];
    }

    private function resolveTask(int $projectId, int $stageId, int $taskId): PmTask
    {
        $stage = $this->resolveStage($projectId, $stageId);

        return PmTask::where('project_stage_id', $stage->id)
            ->where('id', $taskId)
            ->firstOrFail();
    }
}

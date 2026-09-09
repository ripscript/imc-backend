<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Services\DTOs\ProjectDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = Project::query()
            ->with(['client', 'partner', 'category', 'pic', 'attachments']);

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name', 'project_no'], 'updated_at');
    }

    public function detail(string $projectNo)
    {
        return Project::with(['client', 'partner', 'category', 'workflow', 'pic', 'attachments'])
            ->where('project_no', $projectNo)
            ->firstOrFail();
    }

    public function create(ProjectDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $project = new Project;
            $project->name = $dto->name;
            $project->description = $dto->description;
            $project->client_id = $dto->client_id;
            $project->partner_id = $dto->partner_id;
            $project->category_id = $dto->category_id;
            $project->workflow_id = $dto->workflow_id;
            $project->pic_user_id = $dto->pic_user_id;
            $project->status = $dto->status ?? 'draft';
            $project->progress = $dto->progress ?? 0;
            $project->start_date = $dto->start_date;
            $project->target_end_date = $dto->target_end_date;
            $project->created_by = $dto->created_by;
            $project->updated_by = $dto->updated_by;
            $project->save();

            if (empty($project->project_no)) {
                $project->project_no = ProjectCodeGenerator::project($project->id);
                $project->save();
            }

            $this->syncAttachments($project, $dto->attachments);

            return $project->load(['client', 'partner', 'category', 'workflow', 'pic', 'attachments']);
        });
    }

    public function update(string $projectNo, ProjectDTO $dto)
    {
        return DB::transaction(function () use ($projectNo, $dto) {
            $project = Project::where('project_no', $projectNo)->firstOrFail();

            if ($dto->name !== null) {
                $project->name = $dto->name;
            }
            if ($dto->description !== null) {
                $project->description = $dto->description;
            }
            if ($dto->client_id !== null) {
                $project->client_id = $dto->client_id;
            }
            if ($dto->partner_id !== null) {
                $project->partner_id = $dto->partner_id;
            }
            if ($dto->category_id !== null) {
                $project->category_id = $dto->category_id;
            }
            if ($dto->workflow_id !== null) {
                $project->workflow_id = $dto->workflow_id;
            }
            if ($dto->pic_user_id !== null) {
                $project->pic_user_id = $dto->pic_user_id;
            }
            if ($dto->status !== null) {
                $project->status = $dto->status;
            }
            if ($dto->progress !== null) {
                $project->progress = $dto->progress;
            }
            if ($dto->start_date !== null) {
                $project->start_date = $dto->start_date;
            }
            if ($dto->target_end_date !== null) {
                $project->target_end_date = $dto->target_end_date;
            }
            $project->updated_by = $dto->updated_by;
            $project->save();

            if (! empty($dto->deleted_attachment_ids)) {
                ProjectAttachment::where('project_id', $project->id)
                    ->whereIn('id', $dto->deleted_attachment_ids)
                    ->delete();
            }

            if (! empty($dto->attachments)) {
                $this->syncAttachments($project, $dto->attachments);
            }

            return $project->load(['client', 'partner', 'category', 'workflow', 'pic', 'attachments']);
        });
    }

    public function destroy(string $projectNo)
    {
        $project = Project::where('project_no', $projectNo)->firstOrFail();

        if ($project->status !== 'draft') {
            throw new \App\Exceptions\Domain\ValidationException(
                'Hanya proyek berstatus draft yang dapat dihapus.',
                null,
                422
            );
        }

        $project->delete();

        return true;
    }

    protected function syncAttachments(Project $project, array $urls): void
    {
        $urls = array_values(array_filter($urls, fn ($url) => ! empty($url)));

        foreach ($urls as $url) {
            $project->attachments()->create([
                'file_url' => $url,
                'created_by' => $project->created_by,
            ]);
        }
    }
}

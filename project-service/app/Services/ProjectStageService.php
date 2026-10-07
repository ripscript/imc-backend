<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmDeliverable;
use App\Models\PmTask;
use App\Models\PmWorkflowStage;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Services\DTOs\ProjectStageDTO;
use App\Services\DTOs\StageUpdateDTO;
use App\Support\StageDependencies;
use Illuminate\Support\Facades\DB;

class ProjectStageService
{
    use ResolvesProjectStage;

    public function list(int $projectId)
    {
        $project = Project::findOrFail($projectId);

        return $project->stages()->with(['pic', 'workflowStage', 'approver'])->orderBy('sequence')->get();
    }

    public function detail(int $projectId, int $stageId): ProjectStage
    {
        return $this->resolveStage($projectId, $stageId)->load([
            'pic',
            'workflowStage',
            'approver',
            'tasks.assignee',
            'deliverables',
            'documents.uploader',
        ]);
    }

    /**
     * Partial stage update (S5). `requires_approval` is an exit-gate flag
     * (specs/005): it only controls whether completion needs clearance.
     */
    public function updateStage(int $projectId, int $stageId, StageUpdateDTO $dto): ProjectStage
    {
        return DB::transaction(function () use ($projectId, $stageId, $dto) {
            $stage = $this->resolveStage($projectId, $stageId);

            foreach (['name', 'description', 'start_date', 'end_date', 'pic_user_id', 'allow_skip', 'allow_repeat', 'requires_approval'] as $field) {
                if ($dto->isProvided($field)) {
                    $stage->{$field} = $dto->{$field};
                }
            }

            if ($dto->isProvided('status') && $dto->status !== null) {
                if (Project::findOrFail($projectId)->status !== 'draft') {
                    throw new ValidationException(
                        'Status stage tidak dapat diubah setelah proyek diaktivasi.',
                        ['status' => ['Gunakan endpoint complete/skip/reopen untuk mengubah status stage.']]
                    );
                }

                $stage->status = $dto->status;

                if ($dto->status === 'completed' && empty($stage->completed_at)) {
                    $stage->completed_at = now();
                }

                if ($dto->status !== 'completed') {
                    $stage->completed_at = null;
                }
            }

            if ($dto->isProvided('depends_on_project_stage_id')) {
                $this->validateDependency($stage, $dto->depends_on_project_stage_id);
                $stage->depends_on_project_stage_id = $dto->depends_on_project_stage_id;
            }

            $stage->is_configured = true;
            $stage->updated_by = $dto->updated_by;
            $stage->save();

            if ($dto->isProvided('tasks') && is_array($dto->tasks)) {
                $this->replaceChildren($stage, ['tasks' => $dto->tasks], $dto->updated_by);
            }

            if ($dto->isProvided('deliverables') && is_array($dto->deliverables)) {
                $this->replaceChildren($stage, ['deliverables' => $dto->deliverables], $dto->updated_by);
            }

            if ($dto->isProvided('pic_user_id') && $dto->pic_user_id) {
                (new PmProjectMemberService)->syncPic($stage->project_id, (int) $dto->pic_user_id, $dto->updated_by);
            }

            return $this->detail($projectId, $stage->id);
        });
    }

    /**
     * Bulk save of wizard step 4 (S5) for many stages in one call.
     *
     * Split semantics: content (fields, tasks, deliverables) is
     * applied only to the listed stages (partial top level — unlisted stages
     * keep their content), while `is_configured` is swept to true for every
     * stage of the project. A single call therefore declares step 4 done.
     *
     * Atomic: one outer transaction (nested service transactions become
     * savepoints), so any failure rolls the whole batch back. Domain errors
     * are re-thrown with a `stages.{index}.` key prefix so FE can jump to
     * the offending stage.
     */
    public function configureAll(int $projectId, array $entries, ?string $actor = null)
    {
        return DB::transaction(function () use ($projectId, $entries, $actor) {
            $project = Project::findOrFail($projectId);

            $stages = ProjectStage::where('project_id', $project->id)->get()->keyBy('id');

            if ($stages->isEmpty()) {
                throw new ValidationException(
                    'Proyek ini belum memiliki stage.',
                    ['stages' => ['Tambahkan stage terlebih dahulu sebelum menyimpan konfigurasi.']]
                );
            }

            foreach ($entries as $index => $entry) {
                $stageId = isset($entry['id']) ? (int) $entry['id'] : null;

                if ($stageId === null || ! $stages->has($stageId)) {
                    throw new ValidationException(
                        'Stage tidak ditemukan pada proyek ini.',
                        ["stages.{$index}.id" => ['Stage id '.($entry['id'] ?? '?')." bukan bagian dari proyek {$projectId}."]]
                    );
                }
            }

            foreach ($entries as $index => $entry) {
                $stageId = (int) $entry['id'];
                $payload = $entry;
                unset($payload['id']);

                $dto = new StageUpdateDTO($payload);
                $dto->updated_by = $actor;

                try {
                    $this->updateStage($projectId, $stageId, $dto);
                } catch (ValidationException $e) {
                    throw new ValidationException(
                        $e->getMessage(),
                        $this->prefixErrors("stages.{$index}", $e->getErrors())
                    );
                }
            }

            // Any bulk save declares step 4 configured for the whole project.
            // Only the flag is swept — unlisted stages keep their content.
            ProjectStage::where('project_id', $project->id)
                ->where('is_configured', false)
                ->update(['is_configured' => true]);

            return $project->stages()->with(['pic', 'workflowStage', 'approver'])->orderBy('sequence')->get();
        });
    }

    /**
     * Prefix every error key with the bulk entry path (e.g. `stages.2.`).
     */
    private function prefixErrors(string $prefix, mixed $errors): array
    {
        if (! is_array($errors)) {
            return [$prefix => (array) $errors];
        }

        $prefixed = [];

        foreach ($errors as $key => $messages) {
            $prefixed["{$prefix}.{$key}"] = $messages;
        }

        return $prefixed;
    }

    private function validateDependency(ProjectStage $stage, mixed $dependsOn): void
    {
        if ($dependsOn === null) {
            return;
        }

        if ((int) $dependsOn === (int) $stage->id) {
            throw new ValidationException(
                'Stage tidak dapat bergantung pada dirinya sendiri.',
                ['depends_on_project_stage_id' => ['Stage tidak dapat bergantung pada dirinya sendiri.']]
            );
        }

        $siblingIds = ProjectStage::where('project_id', $stage->project_id)->pluck('id')->all();

        if (! in_array((int) $dependsOn, array_map('intval', $siblingIds), true)) {
            throw new ValidationException(
                'Dependency stage tidak valid.',
                ['depends_on_project_stage_id' => ["Stage id {$dependsOn} bukan bagian dari proyek ini."]]
            );
        }

        $links = ProjectStage::where('project_id', $stage->project_id)
            ->pluck('depends_on_project_stage_id', 'id')
            ->all();
        $links[$stage->id] = $dependsOn;

        StageDependencies::assertAcyclic($links);
    }

    /**
     * Bulk replace-all sync of a project's stages (S3: pure store).
     *
     * The template is FE-held: FE fetches the blueprint from master,
     * merges it in its own state, and submits the full tree here. Each
     * entry stores exactly what is sent — `workflow_stage_id` is only a
     * provenance link (exists-validated). Nested `tasks[]`/`deliverables[]`
     * replace the stage's children when the keys are present. Request
     * `sequence` values only define the order — stored sequences are
     * reindexed to 1..n.
     */
    public function sync(int $projectId, ProjectStageDTO $dto)
    {
        return DB::transaction(function () use ($projectId, $dto) {
            $project = Project::findOrFail($projectId);

            $entries = collect($dto->stages ?? [])->sortBy('sequence')->values();

            $existing = ProjectStage::where('project_id', $project->id)->get()->keyBy('id');

            foreach ($entries as $entry) {
                if (! empty($entry['id']) && ! $existing->has($entry['id'])) {
                    throw new ValidationException(
                        'Stage tidak ditemukan pada proyek ini.',
                        ['stages' => ["Stage id {$entry['id']} bukan bagian dari proyek {$projectId}."]]
                    );
                }
            }

            $keptIds = $entries->pluck('id')->filter()->all();
            $deletedIds = $existing->keys()->diff($keptIds)->all();

            if ($deletedIds) {
                $blocked = ProjectStage::where('project_id', $project->id)
                    ->whereIn('depends_on_project_stage_id', $deletedIds)
                    ->whereNotIn('id', $deletedIds)
                    ->get();

                if ($blocked->isNotEmpty()) {
                    throw new ValidationException(
                        'Stage tidak dapat dihapus karena masih menjadi dependency stage lain.',
                        ['stages' => $blocked->map(fn (ProjectStage $s) => "Stage '{$s->name}' bergantung pada stage yang dihapus.")->all()]
                    );
                }
            }

            $this->validateTemplateLinks($entries, $dto, $project);

            if ($dto->workflowIdProvided) {
                $project->workflow_id = $dto->workflow_id;
                $project->updated_by = $dto->updated_by;
                $project->save();
            }

            foreach ($deletedIds as $deletedId) {
                $existing[$deletedId]->delete();
            }

            $sequence = 1;
            foreach ($entries as $entry) {
                $attributes = [
                    'workflow_stage_id' => $entry['workflow_stage_id'] ?? null,
                    'name' => $entry['name'],
                    'sequence' => $sequence++,
                    'is_required' => $entry['is_required'] ?? true,
                    'allow_skip' => $entry['allow_skip'] ?? false,
                    'allow_repeat' => $entry['allow_repeat'] ?? false,
                    'updated_by' => $dto->updated_by,
                ];

                if (array_key_exists('description', $entry)) {
                    $attributes['description'] = $entry['description'];
                }
                if (array_key_exists('requires_approval', $entry)) {
                    $attributes['requires_approval'] = $entry['requires_approval'];
                }

                if (! empty($entry['id'])) {
                    $stage = $existing[$entry['id']];
                    $stage->fill($attributes);
                    $stage->save();
                } else {
                    $stage = ProjectStage::create(array_merge($attributes, [
                        'project_id' => $project->id,
                        'status' => 'pending',
                        'progress' => 0,
                        'is_configured' => false,
                        'created_by' => $dto->created_by ?? $dto->updated_by,
                    ]));
                }

                $this->replaceChildren($stage, $entry, $dto->updated_by);
            }

            return $project->stages()->with(['pic', 'workflowStage', 'approver'])->orderBy('sequence')->get();
        });
    }

    /**
     * Provenance-link validation: every referenced template stage must
     * exist and, when the project targets a workflow, belong to it.
     * Templates are never read for content here — FE sends the full tree.
     */
    protected function validateTemplateLinks($entries, ProjectStageDTO $dto, Project $project): void
    {
        $templateIds = $entries->pluck('workflow_stage_id')->filter()->unique()->all();

        if (empty($templateIds)) {
            return;
        }

        $templates = PmWorkflowStage::whereIn('id', $templateIds)->get()->keyBy('id');

        $workflowId = $dto->workflowIdProvided ? $dto->workflow_id : $project->workflow_id;

        foreach ($templateIds as $templateId) {
            $template = $templates->get($templateId);

            if (! $template) {
                throw new ValidationException(
                    'Template stage tidak valid.',
                    ['stages' => ["workflow_stage_id {$templateId} tidak ditemukan."]]
                );
            }

            if ($workflowId && (int) $template->workflow_id !== (int) $workflowId) {
                throw new ValidationException(
                    'Template stage berasal dari workflow yang berbeda.',
                    ['stages' => ["Stage '{$template->name}' bukan bagian dari workflow yang dipilih."]]
                );
            }
        }
    }

    /**
     * Replace a stage's children from a nested payload. Each key is only
     * touched when present: an explicit empty array clears that set.
     * Entries with `id` update the matching row (422 when foreign);
     * entries without `id` are created; missing rows are soft-deleted.
     */
    protected function replaceChildren(ProjectStage $stage, array $entry, ?string $actor = null): void
    {
        if (array_key_exists('tasks', $entry) && is_array($entry['tasks'])) {
            $this->replaceTasks($stage, $entry['tasks'], $actor);
        }

        if (array_key_exists('deliverables', $entry) && is_array($entry['deliverables'])) {
            $this->replaceDeliverables($stage, $entry['deliverables'], $actor);
        }
    }

    private function replaceTasks(ProjectStage $stage, array $items, ?string $actor = null): void
    {
        $rows = PmTask::where('project_stage_id', $stage->id)->get()->keyBy('id');
        $keep = [];

        foreach ($items as $index => $item) {
            if (! empty($item['id'])) {
                $task = $rows->get($item['id']);

                if (! $task) {
                    throw new ValidationException(
                        'Task tidak ditemukan pada stage ini.',
                        ["tasks.{$index}.id" => ["Task id {$item['id']} bukan bagian dari stage {$stage->id}."]]
                    );
                }

                foreach (['title', 'description', 'status', 'priority', 'assignee_id', 'due_date'] as $field) {
                    if (array_key_exists($field, $item)) {
                        $task->{$field} = $item[$field];
                    }
                }
                $task->updated_by = $actor;
                $task->save();
                $keep[] = $task->id;
            } else {
                if (empty($item['title'])) {
                    throw new ValidationException(
                        'Judul task wajib diisi.',
                        ["tasks.{$index}.title" => ['Judul task wajib diisi untuk task baru.']]
                    );
                }

                $keep[] = PmTask::create([
                    'project_stage_id' => $stage->id,
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'status' => $item['status'] ?? 'pending',
                    'priority' => $item['priority'] ?? 'medium',
                    'assignee_id' => $item['assignee_id'] ?? null,
                    'due_date' => $item['due_date'] ?? null,
                    'created_by' => $actor,
                    'updated_by' => $actor,
                ])->id;
            }
        }

        PmTask::where('project_stage_id', $stage->id)
            ->whereNotIn('id', $keep)
            ->get()
            ->each(fn (PmTask $task) => $task->delete());
    }

    private function replaceDeliverables(ProjectStage $stage, array $items, ?string $actor = null): void
    {
        $rows = PmDeliverable::where('project_stage_id', $stage->id)->get()->keyBy('id');
        $keep = [];

        foreach ($items as $index => $item) {
            if (! empty($item['id'])) {
                $deliverable = $rows->get($item['id']);

                if (! $deliverable) {
                    throw new ValidationException(
                        'Deliverable tidak ditemukan pada stage ini.',
                        ["deliverables.{$index}.id" => ["Deliverable id {$item['id']} bukan bagian dari stage {$stage->id}."]]
                    );
                }

                foreach (['name', 'document_type_id', 'is_required', 'status', 'due_date'] as $field) {
                    if (array_key_exists($field, $item)) {
                        $deliverable->{$field} = $item[$field];
                    }
                }
                $deliverable->updated_by = $actor;
                $deliverable->save();
                $keep[] = $deliverable->id;
            } else {
                if (empty($item['name'])) {
                    throw new ValidationException(
                        'Nama deliverable wajib diisi.',
                        ["deliverables.{$index}.name" => ['Nama deliverable wajib diisi untuk deliverable baru.']]
                    );
                }

                $keep[] = PmDeliverable::create([
                    'project_stage_id' => $stage->id,
                    'document_type_id' => $item['document_type_id'] ?? null,
                    'name' => $item['name'],
                    'is_required' => $item['is_required'] ?? true,
                    'status' => $item['status'] ?? 'pending',
                    'due_date' => $item['due_date'] ?? null,
                    'created_by' => $actor,
                    'updated_by' => $actor,
                ])->id;
            }
        }

        PmDeliverable::where('project_stage_id', $stage->id)
            ->whereNotIn('id', $keep)
            ->get()
            ->each(fn (PmDeliverable $deliverable) => $deliverable->delete());
    }
}

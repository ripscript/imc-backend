<?php

namespace App\Services;

use App\Models\PmDeliverable;
use App\Services\DTOs\PmDeliverableDTO;

class PmDeliverableService
{
    use ResolvesProjectStage;

    public function list(int $projectId, int $stageId)
    {
        $stage = $this->resolveStage($projectId, $stageId);

        return PmDeliverable::where('project_stage_id', $stage->id)->orderBy('id')->get();
    }

    public function create(int $projectId, int $stageId, PmDeliverableDTO $dto): PmDeliverable
    {
        $stage = $this->resolveStage($projectId, $stageId);

        return PmDeliverable::create([
            'project_stage_id' => $stage->id,
            'name' => $dto->name,
            'document_type_id' => $dto->document_type_id,
            'is_required' => $dto->is_required ?? true,
            'status' => $dto->status ?? 'pending',
            'due_date' => $dto->due_date,
            'created_by' => $dto->created_by,
            'updated_by' => $dto->updated_by,
        ]);
    }

    public function update(int $projectId, int $stageId, int $deliverableId, PmDeliverableDTO $dto): PmDeliverable
    {
        $stage = $this->resolveStage($projectId, $stageId);

        $deliverable = PmDeliverable::where('project_stage_id', $stage->id)
            ->where('id', $deliverableId)
            ->firstOrFail();

        foreach (['name', 'document_type_id', 'is_required', 'status', 'due_date'] as $field) {
            if ($dto->isProvided($field)) {
                $deliverable->{$field} = $dto->{$field};
            }
        }
        $deliverable->updated_by = $dto->updated_by;
        $deliverable->save();

        return $deliverable;
    }

    public function delete(int $projectId, int $stageId, int $deliverableId): void
    {
        $stage = $this->resolveStage($projectId, $stageId);

        PmDeliverable::where('project_stage_id', $stage->id)
            ->where('id', $deliverableId)
            ->firstOrFail()
            ->delete();
    }
}

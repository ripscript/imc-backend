<?php

namespace App\Services\ProjectManagement;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmWorkflow;
use App\Models\PmWorkflowStage;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\WorkflowDTO;
use App\Services\QueryService;
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = PmWorkflow::query();

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name', 'code']);
    }

    public function detail($id)
    {
        return PmWorkflow::with('stages')->findOrFail($id);
    }

    public function stages($id)
    {
        PmWorkflow::findOrFail($id);

        return PmWorkflowStage::where('workflow_id', $id)
            ->orderBy('sequence')
            ->get();
    }

    public function create(WorkflowDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $code = $dto->code;
            $version = 1;

            if ($code) {
                $last = PmWorkflow::withTrashed()
                    ->where('code', $code)
                    ->orderByDesc('version')
                    ->first();
                if ($last) {
                    $version = $last->version + 1;
                }
            }

            $workflow = new PmWorkflow;
            $workflow->name = $dto->name;
            $workflow->code = $code;
            $workflow->version = $version;
            $workflow->status = $dto->status ?? 'active';
            $workflow->created_by = $dto->created_by;
            $workflow->updated_by = $dto->updated_by;
            $workflow->save();

            if (empty($workflow->code)) {
                $workflow->code = ProjectCodeGenerator::workflow($workflow->id);
                $workflow->save();
            }

            $this->syncStages($workflow, $dto->stages ?? []);

            return $workflow;
        });
    }

    public function update($id, WorkflowDTO $dto)
    {
        return DB::transaction(function () use ($id, $dto) {
            $workflow = PmWorkflow::findOrFail($id);

            if ($dto->name !== null) {
                $workflow->name = $dto->name;
            }
            if ($dto->status !== null) {
                $workflow->status = $dto->status;
            }
            $workflow->updated_by = $dto->updated_by;
            $workflow->save();

            if ($dto->stages !== null) {
                $this->syncStages($workflow, $dto->stages);
            }

            return $workflow;
        });
    }

    public function destroy($id)
    {
        PmWorkflow::findOrFail($id)->delete();

        return true;
    }

    protected function syncStages(PmWorkflow $workflow, array $stages)
    {
        $seenSequences = [];
        $seenCodes = [];

        foreach ($stages as $stage) {
            $sequence = (int) ($stage['sequence'] ?? 0);
            $code = $stage['code'] ?? null;

            if ($sequence < 1) {
                throw new ValidationException('Sequence stage tidak valid');
            }
            if (isset($seenSequences[$sequence])) {
                throw new ValidationException("Duplicate sequence {$sequence}");
            }
            if ($code && isset($seenCodes[$code])) {
                throw new ValidationException("Duplicate code stage {$code}");
            }

            $seenSequences[$sequence] = true;
            $seenCodes[$code] = true;
        }

        PmWorkflowStage::where('workflow_id', $workflow->id)->delete();

        foreach ($stages as $stage) {
            $newStage = new PmWorkflowStage;
            $newStage->workflow_id = $workflow->id;
            $newStage->name = $stage['name'];
            $newStage->code = $stage['code'] ?? null;
            $newStage->sequence = (int) $stage['sequence'];
            $newStage->is_required = $stage['is_required'] ?? true;
            $newStage->allow_skip = $stage['allow_skip'] ?? false;
            $newStage->allow_repeat = $stage['allow_repeat'] ?? false;
            $newStage->created_by = $workflow->created_by;
            $newStage->updated_by = $workflow->updated_by;
            $newStage->save();

            if (empty($newStage->code)) {
                $newStage->code = ProjectCodeGenerator::workflowStage($workflow->id, $newStage->sequence);
                $newStage->save();
            }
        }
    }
}

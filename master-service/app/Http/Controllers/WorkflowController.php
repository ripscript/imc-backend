<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateWorkflowRequest;
use App\Http\Requests\UpdateWorkflowRequest;
use App\Http\Resources\WorkflowResource;
use App\Http\Resources\WorkflowStageResource;
use App\Models\PmWorkflow;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\WorkflowDTO;
use App\Services\ProjectManagement\WorkflowService;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct(
        protected WorkflowService $workflowService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PmWorkflow::class);

        $data = $this->workflowService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data workflow');
    }

    public function detail($id)
    {
        $workflow = $this->workflowService->detail($id);
        $this->authorize('view', $workflow);

        return ApiResponse::success(new WorkflowResource($workflow), 'Berhasil mengambil data detail workflow');
    }

    public function create(CreateWorkflowRequest $request)
    {
        $this->authorize('create', PmWorkflow::class);

        $dto = new WorkflowDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->workflowService->create($dto);

        $totalStages = $result->stages()->count();
        $result->total_stages = $totalStages;

        return ApiResponse::success(new WorkflowResource($result), "Workflow dibuat dengan {$totalStages} stage", 201);
    }

    public function update(UpdateWorkflowRequest $request, $id)
    {
        $dto = new WorkflowDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->workflowService->update($id, $dto);
        $this->authorize('update', $result);

        $result->total_stages = $result->stages()->count();

        return ApiResponse::success(new WorkflowResource($result), 'Workflow diperbarui');
    }

    public function destroy($id)
    {
        $this->authorize('delete', PmWorkflow::class);

        $this->workflowService->destroy($id);

        return ApiResponse::success('', 'Workflow dihapus');
    }

    public function stages($id)
    {
        $this->authorize('viewAny', PmWorkflow::class);

        $stages = $this->workflowService->stages($id);

        return ApiResponse::success(WorkflowStageResource::collection($stages), 'Berhasil mengambil data stage workflow');
    }
}

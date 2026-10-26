<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\PmTaskResource;
use App\Services\DTOs\PmTaskDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\PmTaskService;

class StageTaskController extends Controller
{
    public function __construct(
        protected PmTaskService $taskService
    ) {}

    public function index(\Illuminate\Http\Request $request, $project_id, $stage_id)
    {
        $result = $this->taskService->list(
            (int) $project_id,
            (int) $stage_id,
            QueryFilterDTO::fromRequest($request)
        );

        return ApiResponse::success([
            'current_page' => $result['meta']['page'],
            'per_page' => $result['meta']['limit'],
            'total' => $result['meta']['total'],
            'items' => PmTaskResource::collection($result['data']),
        ], 'Berhasil mengambil data task');
    }

    public function store(StoreTaskRequest $request, $project_id, $stage_id)
    {
        $user = auth('admin')->user();

        $dto = new PmTaskDTO($request->validated());
        $dto->created_by = $user?->name;
        $dto->updated_by = $user?->name;

        $task = $this->taskService->create((int) $project_id, (int) $stage_id, $dto);

        return ApiResponse::success(new PmTaskResource($task), 'Task dibuat', 201);
    }

    public function update(UpdateTaskRequest $request, $project_id, $stage_id, $task_id)
    {
        $dto = new PmTaskDTO($request->validated());
        $dto->updated_by = auth('admin')->user()?->name;

        $task = $this->taskService->update((int) $project_id, (int) $stage_id, (int) $task_id, $dto);

        return ApiResponse::success(new PmTaskResource($task), 'Task diperbarui');
    }

    public function destroy($project_id, $stage_id, $task_id)
    {
        $this->taskService->delete((int) $project_id, (int) $stage_id, (int) $task_id);

        return ApiResponse::success('', 'Task dihapus');
    }

    public function toggle($project_id, $task_id)
    {
        $result = $this->taskService->toggle(
            (int) $project_id,
            (int) $task_id,
            auth('admin')->user()?->name
        );

        return ApiResponse::success($result, 'Status task diperbarui');
    }
}

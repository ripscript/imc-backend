<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\BulkStageConfigurationRequest;
use App\Http\Requests\RejectStageClearanceRequest;
use App\Http\Requests\RequestStageClearanceRequest;
use App\Http\Requests\SyncStagesRequest;
use App\Http\Requests\UpdateStageRequest;
use App\Http\Resources\ProjectStageDetailResource;
use App\Http\Resources\ProjectStageResource;
use App\Services\DTOs\ProjectStageDTO;
use App\Services\DTOs\StageUpdateDTO;
use App\Services\ProjectSetupStateService;
use App\Services\ProjectStageService;
use App\Services\StageLifecycleService;

class ProjectStageController extends Controller
{
    public function __construct(
        protected ProjectStageService $stageService,
        protected ProjectSetupStateService $setupStateService,
        protected StageLifecycleService $lifecycleService
    ) {}

    public function index($project_id)
    {
        $stages = $this->stageService->list((int) $project_id);

        return ApiResponse::success(
            ProjectStageResource::collection($stages),
            'Berhasil mengambil data stage proyek'
        );
    }

    public function sync(SyncStagesRequest $request, $project_id)
    {
        $user = auth('admin')->user();

        $dto = new ProjectStageDTO($request->validated());
        $dto->created_by = $user?->name;
        $dto->updated_by = $user?->name;

        $stages = $this->stageService->sync((int) $project_id, $dto);

        return ApiResponse::success(
            ProjectStageResource::collection($stages),
            'Stage proyek disimpan'
        );
    }

    public function show($project_id, $stage_id)
    {
        $stage = $this->stageService->detail((int) $project_id, (int) $stage_id);

        return ApiResponse::success(
            new ProjectStageDetailResource($stage),
            'Berhasil mengambil konfigurasi stage'
        );
    }

    public function update(UpdateStageRequest $request, $project_id, $stage_id)
    {
        $dto = new StageUpdateDTO($request->validated());
        $dto->updated_by = auth('admin')->user()?->name;

        $stage = $this->stageService->updateStage((int) $project_id, (int) $stage_id, $dto);

        return ApiResponse::success(
            new ProjectStageDetailResource($stage),
            'Konfigurasi stage disimpan'
        );
    }

    /**
     * Bulk save of wizard step 4: many stages (with nested tasks and
     * deliverables) in one atomic request. Only the listed
     * stages get their content updated; `is_configured` is marked for all
     * of the project's stages. Returns the fresh stage list plus the
     * wizard setup-state so FE updates completion in one round trip.
     */
    public function configureAll(BulkStageConfigurationRequest $request, $project_id)
    {
        $projectId = (int) $project_id;

        $stages = $this->stageService->configureAll(
            $projectId,
            $request->validated()['stages'],
            auth('admin')->user()?->name
        );

        return ApiResponse::success([
            'stages' => ProjectStageResource::collection($stages),
            'setup_state' => $this->setupStateService->state($projectId),
        ], 'Konfigurasi stage disimpan');
    }

    /**
     * Gated completion of an in-progress stage (execution, post-activation).
     */
    public function complete($project_id, $stage_id)
    {
        $result = $this->lifecycleService->complete(
            (int) $project_id,
            (int) $stage_id,
            auth('admin')->user()
        );

        return ApiResponse::success($this->lifecyclePayload($result), 'Stage diselesaikan');
    }

    public function requestClearance(RequestStageClearanceRequest $request, $project_id, $stage_id)
    {
        $result = $this->lifecycleService->requestClearance(
            (int) $project_id,
            (int) $stage_id,
            auth('admin')->user(),
            $request->validated('notes')
        );

        return ApiResponse::success($this->lifecyclePayload($result), 'Clearance stage berhasil diajukan');
    }

    public function rejectClearance(RejectStageClearanceRequest $request, $project_id, $stage_id)
    {
        $result = $this->lifecycleService->rejectClearance(
            (int) $project_id,
            (int) $stage_id,
            auth('admin')->user(),
            $request->validated('notes')
        );

        return ApiResponse::success($this->lifecyclePayload($result), 'Clearance stage ditolak');
    }

    public function skip($project_id, $stage_id)
    {
        $result = $this->lifecycleService->skip(
            (int) $project_id,
            (int) $stage_id,
            auth('admin')->user()
        );

        return ApiResponse::success($this->lifecyclePayload($result), 'Stage dilewati');
    }

    public function reopen($project_id, $stage_id)
    {
        $result = $this->lifecycleService->reopen(
            (int) $project_id,
            (int) $stage_id,
            auth('admin')->user()
        );

        return ApiResponse::success($this->lifecyclePayload($result), 'Stage dibuka kembali');
    }

    private function lifecyclePayload(array $result): array
    {
        return [
            'stage' => new ProjectStageResource($result['stage']),
            'project_status' => $result['project_status'],
            'next_stage' => $result['next_stage'] ? new ProjectStageResource($result['next_stage']) : null,
        ];
    }
}

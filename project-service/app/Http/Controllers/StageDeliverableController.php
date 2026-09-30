<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreDeliverableRequest;
use App\Http\Requests\UpdateDeliverableRequest;
use App\Http\Resources\PmDeliverableResource;
use App\Services\DTOs\PmDeliverableDTO;
use App\Services\PmDeliverableService;

class StageDeliverableController extends Controller
{
    public function __construct(
        protected PmDeliverableService $deliverableService
    ) {}

    public function index($project_id, $stage_id)
    {
        $deliverables = $this->deliverableService->list((int) $project_id, (int) $stage_id);

        return ApiResponse::success(
            PmDeliverableResource::collection($deliverables),
            'Berhasil mengambil data deliverable'
        );
    }

    public function store(StoreDeliverableRequest $request, $project_id, $stage_id)
    {
        $user = auth('admin')->user();

        $dto = new PmDeliverableDTO($request->validated());
        $dto->created_by = $user?->name;
        $dto->updated_by = $user?->name;

        $deliverable = $this->deliverableService->create((int) $project_id, (int) $stage_id, $dto);

        return ApiResponse::success(new PmDeliverableResource($deliverable), 'Deliverable dibuat', 201);
    }

    public function update(UpdateDeliverableRequest $request, $project_id, $stage_id, $deliverable_id)
    {
        $dto = new PmDeliverableDTO($request->validated());
        $dto->updated_by = auth('admin')->user()?->name;

        $deliverable = $this->deliverableService->update((int) $project_id, (int) $stage_id, (int) $deliverable_id, $dto);

        return ApiResponse::success(new PmDeliverableResource($deliverable), 'Deliverable diperbarui');
    }

    public function destroy($project_id, $stage_id, $deliverable_id)
    {
        $this->deliverableService->delete((int) $project_id, (int) $stage_id, (int) $deliverable_id);

        return ApiResponse::success('', 'Deliverable dihapus');
    }
}

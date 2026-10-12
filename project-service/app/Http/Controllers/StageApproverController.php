<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\SyncApproversRequest;
use App\Http\Resources\StageApproverResource;
use App\Models\ProjectStage;

class StageApproverController extends Controller
{
    public function index(int|string $project_id, int|string $stage_id)
    {
        $stage = ProjectStage::where('project_id', $project_id)
            ->where('id', $stage_id)
            ->firstOrFail();

        $approvers = $stage->approvers()->with('user')->get();

        return ApiResponse::success(
            StageApproverResource::collection($approvers),
            'Berhasil mengambil data approver'
        );
    }

    public function sync(SyncApproversRequest $request, int|string $project_id, int|string $stage_id)
    {
        $stage = ProjectStage::where('project_id', $project_id)
            ->where('id', $stage_id)
            ->firstOrFail();

        $userIds = collect($request->validated('approvers'))
            ->pluck('approver_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $stage->approverUsers()->sync($userIds);

        $approvers = $stage->approvers()->with('user')->get();

        return ApiResponse::success(
            StageApproverResource::collection($approvers),
            'Approver stage disimpan'
        );
    }
}

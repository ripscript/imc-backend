<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\SyncApproversRequest;
use App\Http\Resources\PmApprovalResource;
use App\Services\PmApprovalService;

class StageApproverController extends Controller
{
    public function __construct(
        protected PmApprovalService $approvalService
    ) {}

    public function index($project_id, $stage_id)
    {
        $approvers = $this->approvalService->list((int) $project_id, (int) $stage_id);

        return ApiResponse::success(
            PmApprovalResource::collection($approvers),
            'Berhasil mengambil data approver'
        );
    }

    public function sync(SyncApproversRequest $request, $project_id, $stage_id)
    {
        $approvers = $this->approvalService->sync(
            (int) $project_id,
            (int) $stage_id,
            $request->validated()['approvers'],
            auth('admin')->user()?->name
        );

        return ApiResponse::success(
            PmApprovalResource::collection($approvers),
            'Approver stage disimpan'
        );
    }
}

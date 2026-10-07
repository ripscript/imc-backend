<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateChangeRequestRequest;
use App\Http\Requests\RejectChangeRequestRequest;
use App\Http\Resources\PmChangeRequestResource;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\PmChangeRequestService;
use Illuminate\Http\Request;

class ProjectChangeRequestController extends Controller
{
    public function __construct(
        protected PmChangeRequestService $changeRequestService
    ) {}

    public function index(Request $request, $project_id)
    {
        $result = $this->changeRequestService->list(
            (int) $project_id,
            QueryFilterDTO::fromRequest($request)
        );

        return ApiResponse::success([
            'current_page' => $result['meta']['page'],
            'per_page' => $result['meta']['limit'],
            'total' => $result['meta']['total'],
            'items' => PmChangeRequestResource::collection($result['data']),
        ], 'Berhasil mengambil data change request');
    }

    public function store(CreateChangeRequestRequest $request, $project_id)
    {
        $cr = $this->changeRequestService->create(
            (int) $project_id,
            $request->validated(),
            auth('admin')->user()
        );

        return ApiResponse::success(
            new PmChangeRequestResource($cr->load(['client', 'stage', 'requester'])),
            'Change request dicatat dan menunggu approval',
            201
        );
    }

    public function approve($project_id, $change_request_id)
    {
        $cr = $this->changeRequestService->approve(
            (int) $project_id,
            (int) $change_request_id,
            auth('admin')->user()
        );

        return ApiResponse::success(new PmChangeRequestResource($cr), 'Change request disetujui');
    }

    public function reject(RejectChangeRequestRequest $request, $project_id, $change_request_id)
    {
        $cr = $this->changeRequestService->reject(
            (int) $project_id,
            (int) $change_request_id,
            auth('admin')->user(),
            $request->validated()['notes']
        );

        return ApiResponse::success(new PmChangeRequestResource($cr), 'Change request ditolak');
    }

    public function apply($project_id, $change_request_id)
    {
        $cr = $this->changeRequestService->apply(
            (int) $project_id,
            (int) $change_request_id,
            auth('admin')->user()
        );

        return ApiResponse::success(new PmChangeRequestResource($cr), 'Change request diterapkan ke proyek');
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\RejectApprovalRequest;
use App\Http\Resources\UnifiedApprovalResource;
use App\Services\UnifiedApprovalService;
use Illuminate\Http\Request;

class UnifiedApprovalController extends Controller
{
    public function __construct(
        protected UnifiedApprovalService $approvalService
    ) {}

    public function index(Request $request)
    {
        $caller = $request->user('admin') ?? $request->user() ?? auth('admin')->user();

        $paginator = $this->approvalService->list($caller, [
            'status' => $request->has('status') ? $request->input('status') : 'pending',
            'type' => $request->input('type'),
            'search' => $request->input('search'),
            'per_page' => $request->input('per_page', 10),
            'page' => $request->input('page', 1),
        ]);

        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => 'Berhasil mengambil daftar approval',
            'data' => UnifiedApprovalResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function approve(Request $request, $id)
    {
        $caller = $request->user('admin') ?? $request->user() ?? auth('admin')->user();

        $approval = $this->approvalService->approve((int) $id, $caller);

        return ApiResponse::success(
            new UnifiedApprovalResource($approval),
            'Permintaan approval berhasil disetujui'
        );
    }

    public function reject(RejectApprovalRequest $request, $id)
    {
        $caller = $request->user('admin') ?? $request->user() ?? auth('admin')->user();

        $approval = $this->approvalService->reject(
            (int) $id,
            $caller,
            $request->validated()['notes']
        );

        return ApiResponse::success(
            new UnifiedApprovalResource($approval),
            'Permintaan approval berhasil ditolak'
        );
    }
}

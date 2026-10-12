<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmApprovalRequest;
use App\Models\PmStageApprover;
use App\Models\User;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UnifiedApprovalService
{
    public function __construct(
        protected ProjectActivationService $projectActivationService,
        protected StageLifecycleService $stageLifecycleService,
        protected PmChangeRequestService $changeRequestService
    ) {}

    public function list(User $caller, array|QueryFilterDTO $filters = []): LengthAwarePaginator
    {
        if ($filters instanceof QueryFilterDTO) {
            $status = $filters->status ?? 'pending';
            $type = property_exists($filters, 'type') ? $filters->type : null;
            $search = $filters->search;
            $perPage = $filters->limit ?: 10;
            $page = $filters->page ?: 1;
        } else {
            $status = array_key_exists('status', $filters) ? $filters['status'] : 'pending';
            $type = $filters['type'] ?? null;
            $search = $filters['search'] ?? null;
            $perPage = (int) ($filters['per_page'] ?? $filters['limit'] ?? 10);
            $page = (int) ($filters['page'] ?? 1);
        }

        if (empty($status)) {
            $status = 'pending';
        }

        $perPage = $perPage > 0 ? $perPage : 10;
        $page = max($page, 1);

        $query = PmApprovalRequest::with(['project', 'requester', 'decider']);

        // Authorization Scoping
        $caller->loadMissing('role');
        $isCentralAdmin = $caller->role && in_array($caller->role->code, ['super_admin', 'admin_pusat'], true);

        if (! $isCentralAdmin) {
            $assignedStageIds = PmStageApprover::where('user_id', $caller->id)
                ->pluck('project_stage_id');

            $query->where('pm_approval_requests.type', 'stage_clearance')
                ->whereIn('pm_approval_requests.approvable_id', $assignedStageIds);
        }

        // Status Filter
        if ($status !== 'all') {
            $query->where('pm_approval_requests.status', $status);
        }

        // Type Filter
        if ($type) {
            $query->where('pm_approval_requests.type', $type);
        }

        // Search Filter
        if ($search) {
            $operator = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $operator) {
                $q->where('pm_approval_requests.title', $operator, "%{$search}%")
                    ->orWhere('pm_approval_requests.description', $operator, "%{$search}%")
                    ->orWhereHas('project', function ($pq) use ($search, $operator) {
                        $pq->where('title', $operator, "%{$search}%");
                    });
            });
        }

        $query->orderBy('pm_approval_requests.id', 'desc');

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function approve(int $id, User $caller): PmApprovalRequest
    {
        $approval = PmApprovalRequest::findOrFail($id);

        if (! $approval->isPending()) {
            throw new ConflictHttpException("Permintaan approval sudah diproses dengan status {$approval->status}.");
        }

        $this->assertCanDecide($approval, $caller, 'menyetujui');

        match ($approval->type) {
            'activation' => $this->projectActivationService->approveActivation($approval->project_id, $caller),
            'stage_clearance' => $this->stageLifecycleService->complete($approval->project_id, $approval->approvable_id, $caller),
            'change_request' => $this->changeRequestService->approve($approval->project_id, $approval->approvable_id, $caller),
            default => throw new ValidationException("Tipe approval tidak dikenali: {$approval->type}"),
        };

        return $approval->fresh()->load(['project', 'requester', 'decider']);
    }

    public function reject(int $id, User $caller, string $notes): PmApprovalRequest
    {
        $approval = PmApprovalRequest::findOrFail($id);

        if (! $approval->isPending()) {
            throw new ConflictHttpException("Permintaan approval sudah diproses dengan status {$approval->status}.");
        }

        if (empty(trim($notes))) {
            throw new ValidationException('Alasan penolakan wajib diisi.', ['notes' => ['Alasan penolakan wajib diisi.']]);
        }

        $this->assertCanDecide($approval, $caller, 'menolak');

        match ($approval->type) {
            'activation' => $this->projectActivationService->rejectActivation($approval->project_id, $caller, $notes),
            'stage_clearance' => $this->stageLifecycleService->rejectClearance($approval->project_id, $approval->approvable_id, $caller, $notes),
            'change_request' => $this->changeRequestService->reject($approval->project_id, $approval->approvable_id, $caller, $notes),
            default => throw new ValidationException("Tipe approval tidak dikenali: {$approval->type}"),
        };

        return $approval->fresh()->load(['project', 'requester', 'decider']);
    }

    protected function assertCanDecide(PmApprovalRequest $approval, User $caller, string $action): void
    {
        $caller->loadMissing('role');
        $isCentralAdmin = $caller->role && in_array($caller->role->code, ['super_admin', 'admin_pusat'], true);

        if ($isCentralAdmin) {
            return;
        }

        if ($approval->type !== 'stage_clearance') {
            throw new AccessDeniedHttpException("Anda tidak memiliki kewenangan untuk {$action} pengajuan ini.");
        }

        $isAssigned = PmStageApprover::where('project_stage_id', $approval->approvable_id)
            ->where('user_id', $caller->id)
            ->exists();

        if (! $isAssigned) {
            throw new AccessDeniedHttpException("Anda tidak terdaftar sebagai approver untuk stage ini.");
        }
    }
}

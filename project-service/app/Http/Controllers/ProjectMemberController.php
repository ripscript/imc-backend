<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Resources\PmProjectMemberResource;
use App\Services\PmProjectMemberService;

class ProjectMemberController extends Controller
{
    public function __construct(
        protected PmProjectMemberService $memberService
    ) {}

    public function index($project_id)
    {
        $members = $this->memberService->list((int) $project_id);

        return ApiResponse::success(
            PmProjectMemberResource::collection($members),
            'Berhasil mengambil data anggota proyek'
        );
    }

    public function store(StoreMemberRequest $request, $project_id)
    {
        $member = $this->memberService->add(
            (int) $project_id,
            $request->validated()['user_id'],
            $request->validated()['role'] ?? null,
            auth('admin')->user()?->name
        );

        return ApiResponse::success(new PmProjectMemberResource($member), 'Anggota proyek ditambahkan', 201);
    }

    public function destroy($project_id, $member_id)
    {
        $this->memberService->remove((int) $project_id, (int) $member_id);

        return ApiResponse::success('', 'Anggota proyek dihapus');
    }
}

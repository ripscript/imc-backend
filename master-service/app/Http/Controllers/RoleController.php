<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index()
    {
        $data = $this->roleService->index();

        return ApiResponse::success(RoleResource::collection($data), 'Data role berhasil diambil');
    }
}

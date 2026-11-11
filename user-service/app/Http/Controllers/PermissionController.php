<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreatePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Services\DTOs\PermissionDTO;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function index(Request $request)
    {
        $data = $this->permissionService->index($request);

        if ($request->boolean('all', false)) {
            return ApiResponse::success(
                PermissionResource::collection($data),
                'Berhasil mengambil daftar hak akses'
            );
        }

        return ApiResponse::success(
            PermissionResource::collection($data)->response()->getData(true),
            'Berhasil mengambil daftar hak akses'
        );
    }

    public function detail(int|string $id)
    {
        $permission = $this->permissionService->detail($id);

        return ApiResponse::success(
            new PermissionResource($permission),
            'Berhasil mengambil detail hak akses'
        );
    }

    public function create(CreatePermissionRequest $request)
    {
        $dto = new PermissionDTO($request->validated());
        $permission = $this->permissionService->create($dto);

        return ApiResponse::success(
            new PermissionResource($permission),
            'Berhasil menambahkan hak akses',
            201
        );
    }

    public function update(UpdatePermissionRequest $request, int|string $id)
    {
        $dto = new PermissionDTO($request->validated());
        $permission = $this->permissionService->update($id, $dto);

        return ApiResponse::success(
            new PermissionResource($permission),
            'Berhasil memperbarui hak akses'
        );
    }

    public function destroy(int|string $id)
    {
        $this->permissionService->destroy($id);

        return ApiResponse::success(
            null,
            'Berhasil menghapus hak akses'
        );
    }
}

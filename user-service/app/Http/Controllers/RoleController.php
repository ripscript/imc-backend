<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\DTOs\RoleDTO;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index(Request $request)
    {
        $data = $this->roleService->index($request);

        if ($request->boolean('all', false) || ! $request->has('limit')) {
            return ApiResponse::success(
                RoleResource::collection($data),
                'Berhasil mengambil daftar peran'
            );
        }

        return ApiResponse::success(
            RoleResource::collection($data)->response()->getData(true),
            'Berhasil mengambil daftar peran'
        );
    }

    public function detail(int|string $id)
    {
        $role = $this->roleService->detail($id);

        return ApiResponse::success(
            new RoleResource($role),
            'Berhasil mengambil detail peran'
        );
    }

    public function create(CreateRoleRequest $request)
    {
        $dto = new RoleDTO($request->validated());
        $dto->created_by = auth('admin')->user()?->name ?? 'system';
        $dto->updated_by = auth('admin')->user()?->name ?? 'system';

        $role = $this->roleService->create($dto);

        return ApiResponse::success(
            new RoleResource($role),
            'Berhasil menambahkan peran',
            201
        );
    }

    public function update(UpdateRoleRequest $request, int|string $id)
    {
        $dto = new RoleDTO($request->validated());
        $dto->updated_by = auth('admin')->user()?->name ?? 'system';

        $role = $this->roleService->update($id, $dto);

        return ApiResponse::success(
            new RoleResource($role),
            'Berhasil memperbarui peran'
        );
    }

    public function destroy(int|string $id)
    {
        $this->roleService->destroy($id);

        return ApiResponse::success(
            null,
            'Berhasil menghapus peran'
        );
    }
}

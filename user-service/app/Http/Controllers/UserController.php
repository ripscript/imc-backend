<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\UserDTO;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', User::class);

            $data = $this->userService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data pengguna');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $user = $this->userService->detail($id);
            $this->authorize('view', $user);

            return ApiResponse::success(new UserResource($user), 'Berhasil mengambil data detail pengguna');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateUserRequest $request)
    {
        try {
            $this->authorize('create', User::class);

            $dto = new UserDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->userService->create($dto);

            return ApiResponse::success(new UserResource($result), 'Berhasil menambahkan data pengguna', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $dto = new UserDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->userService->update($id, $dto);
            $this->authorize('update', $result);

            return ApiResponse::success(new UserResource($result), 'Berhasil memperbarui data pengguna');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $this->authorize('delete', $user);

            $this->userService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data pengguna');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

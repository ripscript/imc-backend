<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Services\DTOs\MenuDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\MenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->menuService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data menu');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $menu = $this->menuService->detail($id);

            return ApiResponse::success(new MenuResource($menu), 'Berhasil mengambil data detail menu');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateMenuRequest $request)
    {
        try {
            $dto = new MenuDTO($request->validated());
            $dto->created_by = auth()->user()->name;
            $dto->updated_by = auth()->user()->name;

            $result = $this->menuService->create($dto);

            return ApiResponse::success(new MenuResource($result), 'Berhasil menambahkan data menu', 201);
        } catch (\Exception $e) {
            if ($e->getCode() == 409) {
                return ApiResponse::error($e->getMessage(), null, 409);
            }
            throw $e;
        }
    }

    public function update(UpdateMenuRequest $request, $id)
    {
        try {
            $dto = new MenuDTO($request->validated());
            $dto->updated_by = auth()->user()->name;

            $result = $this->menuService->update($id, $dto);

            return ApiResponse::success(new MenuResource($result), 'Berhasil memperbarui data menu', 200);
        } catch (\Exception $e) {
            if ($e->getCode() == 409) {
                return ApiResponse::error($e->getMessage(), null, 409);
            }
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->menuService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus menu');
        } catch (\Throwable $th) {
            if ($th->getCode() == 403) {
                return ApiResponse::error($th->getMessage(), '', 403);
            }
            if ($th->getCode() == 409) {
                return ApiResponse::error($th->getMessage(), '', 409);
            }
            throw $th;
        }
    }
}

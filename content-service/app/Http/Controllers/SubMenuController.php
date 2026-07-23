<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateSubMenuRequest;
use App\Http\Requests\UpdateSubMenuRequest;
use App\Http\Resources\SubMenuResource;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\SubMenuDTO;
use App\Services\SubMenuService;
use Illuminate\Http\Request;

class SubMenuController extends Controller
{
    public function __construct(
        protected SubMenuService $subMenuService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->subMenuService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data sub menu');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $subMenu = $this->subMenuService->detail($id);

            return ApiResponse::success(new SubMenuResource($subMenu), 'Berhasil mengambil data detail sub menu');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateSubMenuRequest $request)
    {
        try {
            $dto = new SubMenuDTO($request->validated());
            $dto->created_by = auth()->user()->name;
            $dto->updated_by = auth()->user()->name;

            $result = $this->subMenuService->create($dto);

            return ApiResponse::success(new SubMenuResource($result), 'Berhasil menambahkan data sub menu', 201);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update(UpdateSubMenuRequest $request, $id)
    {
        try {
            $dto = new SubMenuDTO($request->validated());
            $dto->updated_by = auth()->user()->name;

            $result = $this->subMenuService->update($id, $dto);

            return ApiResponse::success(new SubMenuResource($result), 'Berhasil memperbarui data sub menu', 200);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->subMenuService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus sub menu');
        } catch (\Throwable $th) {
            if ($th->getCode() == 409) {
                return ApiResponse::error($th->getMessage(), '', 409);
            }
            throw $th;
        }
    }
}

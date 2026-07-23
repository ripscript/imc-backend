<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateChildSubMenuRequest;
use App\Http\Requests\UpdateChildSubMenuRequest;
use App\Http\Resources\ChildSubMenuResource;
use App\Services\ChildSubMenuService;
use App\Services\DTOs\ChildSubMenuDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Http\Request;

class ChildSubMenuController extends Controller
{
    public function __construct(
        protected ChildSubMenuService $childSubMenuService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->childSubMenuService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data child sub menu');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $childSubMenu = $this->childSubMenuService->detail($id);

            return ApiResponse::success(new ChildSubMenuResource($childSubMenu), 'Berhasil mengambil data detail child sub menu');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateChildSubMenuRequest $request)
    {
        try {
            $dto = new ChildSubMenuDTO($request->validated());
            $dto->created_by = auth()->user()->name;
            $dto->updated_by = auth()->user()->name;

            $result = $this->childSubMenuService->create($dto);

            return ApiResponse::success(new ChildSubMenuResource($result), 'Berhasil menambahkan data child sub menu', 201);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update(UpdateChildSubMenuRequest $request, $id)
    {
        try {
            $dto = new ChildSubMenuDTO($request->validated());
            $dto->updated_by = auth()->user()->name;

            $result = $this->childSubMenuService->update($id, $dto);

            return ApiResponse::success(new ChildSubMenuResource($result), 'Berhasil memperbarui data child sub menu', 200);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->childSubMenuService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus child sub menu');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

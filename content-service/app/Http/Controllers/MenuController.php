<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Services\DTOs\MenuDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\MenuService;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Menu::class);
        $data = $this->menuService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data menu');
    }

    public function detail($id)
    {
        $result = $this->menuService->detail($id);
        $this->authorize('view', $result);

        return ApiResponse::success(new MenuResource($result), 'Berhasil mengambil data detail menu');
    }

    public function create(CreateMenuRequest $request)
    {
        $dto = new MenuDTO($request->validated());
        $dto->created_by = auth()->user()->name;
        $dto->updated_by = auth()->user()->name;
        $this->authorize('create', Menu::class);

        $result = $this->menuService->create($dto);

        return ApiResponse::success(new MenuResource($result), 'Berhasil menambahkan data menu', 201);
    }

    public function update(UpdateMenuRequest $request, $id)
    {
        $dto = new MenuDTO($request->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->menuService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new MenuResource($result), 'Berhasil memperbarui data menu', 200);
    }

    public function delete($id)
    {
        $model = Menu::findOrFail($id);
        $this->authorize('delete', $model);
        $this->menuService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus menu');
    }
}

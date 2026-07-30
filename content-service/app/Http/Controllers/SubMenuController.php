<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateSubMenuRequest;
use App\Http\Requests\UpdateSubMenuRequest;
use App\Http\Resources\SubMenuResource;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\SubMenuDTO;
use App\Services\SubMenuService;
use App\Models\SubMenu;
use Illuminate\Http\Request;

class SubMenuController extends Controller
{
    public function __construct(
        protected SubMenuService $subMenuService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', SubMenu::class);
        $data = $this->subMenuService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data sub menu');
    }

    public function detail($id)
    {
        $result = $this->subMenuService->detail($id);
        $this->authorize('view', $result);

        return ApiResponse::success(new SubMenuResource($result), 'Berhasil mengambil data detail sub menu');
    }

    public function create(CreateSubMenuRequest $request)
    {
        $dto = new SubMenuDTO($request->validated());
        $dto->created_by = auth()->user()->name;
        $dto->updated_by = auth()->user()->name;
        $this->authorize('create', SubMenu::class);

        $result = $this->subMenuService->create($dto);

        return ApiResponse::success(new SubMenuResource($result), 'Berhasil menambahkan data sub menu', 201);
    }

    public function update(UpdateSubMenuRequest $request, $id)
    {
        $dto = new SubMenuDTO($request->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->subMenuService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new SubMenuResource($result), 'Berhasil memperbarui data sub menu', 200);
    }

    public function delete($id)
    {
        $this->authorize('delete', SubMenu::class);
        $this->subMenuService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus sub menu');
    }
}

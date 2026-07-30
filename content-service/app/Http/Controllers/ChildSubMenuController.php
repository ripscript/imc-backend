<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateChildSubMenuRequest;
use App\Http\Requests\UpdateChildSubMenuRequest;
use App\Http\Resources\ChildSubMenuResource;
use App\Services\ChildSubMenuService;
use App\Services\DTOs\ChildSubMenuDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Models\ChildSubMenu;
use Illuminate\Http\Request;

class ChildSubMenuController extends Controller
{
    public function __construct(
        protected ChildSubMenuService $childSubMenuService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', ChildSubMenu::class);
        $data = $this->childSubMenuService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data child sub menu');
    }

    public function detail($id)
    {
        $result = $this->childSubMenuService->detail($id);
        $this->authorize('view', $result);

        return ApiResponse::success(new ChildSubMenuResource($result), 'Berhasil mengambil data detail child sub menu');
    }

    public function create(CreateChildSubMenuRequest $request)
    {
        $dto = new ChildSubMenuDTO($request->validated());
        $dto->created_by = auth()->user()->name;
        $dto->updated_by = auth()->user()->name;
        $this->authorize('create', ChildSubMenu::class);

        $result = $this->childSubMenuService->create($dto);

        return ApiResponse::success(new ChildSubMenuResource($result), 'Berhasil menambahkan data child sub menu', 201);
    }

    public function update(UpdateChildSubMenuRequest $request, $id)
    {
        $dto = new ChildSubMenuDTO($request->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->childSubMenuService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new ChildSubMenuResource($result), 'Berhasil memperbarui data child sub menu', 200);
    }

    public function delete($id)
    {
        $this->authorize('delete', ChildSubMenu::class);
        $this->childSubMenuService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus child sub menu');
    }
}

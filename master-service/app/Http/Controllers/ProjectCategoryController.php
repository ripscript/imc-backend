<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateProjectCategoryRequest;
use App\Http\Requests\UpdateProjectCategoryRequest;
use App\Http\Resources\ProjectCategoryResource;
use App\Models\PmProjectCategory;
use App\Services\DTOs\ProjectCategoryDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\ProjectManagement\ProjectCategoryService;
use Illuminate\Http\Request;

class ProjectCategoryController extends Controller
{
    public function __construct(
        protected ProjectCategoryService $projectCategoryService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PmProjectCategory::class);

        $data = $this->projectCategoryService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data kategori proyek');
    }

    public function detail($id)
    {
        $category = $this->projectCategoryService->detail($id);
        $this->authorize('view', $category);

        return ApiResponse::success(new ProjectCategoryResource($category), 'Berhasil mengambil data detail kategori proyek');
    }

    public function create(CreateProjectCategoryRequest $request)
    {
        $this->authorize('create', PmProjectCategory::class);

        $dto = new ProjectCategoryDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->projectCategoryService->create($dto);

        return ApiResponse::success(new ProjectCategoryResource($result), 'Kategori dibuat', 201);
    }

    public function update(UpdateProjectCategoryRequest $request, $id)
    {
        $dto = new ProjectCategoryDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->projectCategoryService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new ProjectCategoryResource($result), 'Kategori diperbarui');
    }

    public function destroy($id)
    {
        $this->authorize('delete', PmProjectCategory::class);

        $this->projectCategoryService->destroy($id);

        return ApiResponse::success('', 'Kategori dihapus');
    }
}

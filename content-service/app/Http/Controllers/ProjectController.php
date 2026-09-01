<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\ContentDetailProject;
use App\Services\DTOs\ProjectDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $projectService) {}

    public function sideMenu()
    {
        $this->authorize('viewAny', ContentDetailProject::class);
        return ApiResponse::success($this->projectService->sideMenu(), 'Berhasil mengambil side menu untuk tipe proyek');
    }

    public function index(Request $request, $contentId)
    {
        $this->authorize('viewAny', ContentDetailProject::class);
        return ApiResponse::success($this->projectService->index(QueryFilterDTO::fromRequest($request), $contentId), 'Berhasil mengambil data proyek');
    }

    public function create(CreateProjectRequest $req)
    {
        $dto = new ProjectDTO($req->validated());
        $dto->created_by = $dto->updated_by = auth()->user()->name;
        $this->authorize('create', ContentDetailProject::class);

        return ApiResponse::success(new ProjectResource($this->projectService->create($dto)), 'Berhasil menambahkan data proyek', 201);
    }

    public function detail($id)
    {
        $result = $this->projectService->detail($id);
        $this->authorize('view', $result);
        return ApiResponse::success(new ProjectResource($result), 'Berhasil mengambil data detail proyek');
    }

    public function update(UpdateProjectRequest $req, $id)
    {
        $dto = new ProjectDTO($req->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->projectService->update($id, $dto);
        $this->authorize('update', $result);
        return ApiResponse::success(new ProjectResource($result), 'Berhasil memperbarui data proyek');
    }

    public function delete($id)
    {
        $model = ContentDetailProject::findOrFail($id);
        $this->authorize('delete', $model);
        $this->projectService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus proyek');
    }
}

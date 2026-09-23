<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectListResource;
use App\Http\Resources\ProjectResource;
use App\Services\DTOs\ProjectDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function sideMenu()
    {
        return ApiResponse::success($this->projectService->sideMenu(), 'Berhasil mengambil side menu untuk tipe proyek');
    }

    public function index(Request $request)
    {
        $result = $this->projectService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success([
            'current_page' => $result['meta']['page'],
            'per_page' => $result['meta']['limit'],
            'total' => $result['meta']['total'],
            'items' => ProjectListResource::collection($result['data']),
        ], 'Berhasil mengambil data proyek');
    }

    public function create(CreateProjectRequest $request)
    {
        $dto = new ProjectDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $project = $this->projectService->create($dto);

        return ApiResponse::success(new ProjectResource($project), 'Proyek dibuat (status draft)', 201);
    }

    public function detail($project_no)
    {
        $project = $this->projectService->detail($project_no);

        return ApiResponse::success(new ProjectResource($project), 'Berhasil mengambil data detail proyek');
    }

    public function update(UpdateProjectRequest $request, $project_no)
    {
        $dto = new ProjectDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $project = $this->projectService->update($project_no, $dto);

        return ApiResponse::success(new ProjectResource($project), 'Proyek diperbarui');
    }

    public function destroy($project_no)
    {
        $this->projectService->destroy($project_no);

        return ApiResponse::success('', 'Proyek dihapus');
    }
}

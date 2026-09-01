<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateRepositoryRequest;
use App\Http\Requests\UpdateRepositoryRequest;
use App\Http\Resources\RepositoryResource;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\RepositoryDTO;
use App\Services\RepositoryService;
use App\Models\ContentDetailRepository;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    public function __construct(protected RepositoryService $repositoryService) {}

    public function sideMenu()
    {
        $this->authorize('viewAny', ContentDetailRepository::class);
        return ApiResponse::success($this->repositoryService->sideMenu(), 'Berhasil mengambil side menu untuk tipe repositori');
    }

    public function index(Request $request, $contentId)
    {
        $this->authorize('viewAny', ContentDetailRepository::class);
        return ApiResponse::success($this->repositoryService->index(QueryFilterDTO::fromRequest($request), $contentId), 'Berhasil mengambil data repositori');
    }

    public function create(CreateRepositoryRequest $req)
    {
        $dto = new RepositoryDTO($req->validated());
        $dto->created_by = $dto->updated_by = auth()->user()->name;
        $this->authorize('create', ContentDetailRepository::class);

        return ApiResponse::success(new RepositoryResource($this->repositoryService->create($dto)), 'Berhasil menambahkan data repository', 201);
    }

    public function detail($id)
    {
        $result = $this->repositoryService->detail($id);
        $this->authorize('view', $result);
        return ApiResponse::success(new RepositoryResource($result), 'Berhasil mengambil data detail repository');
    }

    public function update(UpdateRepositoryRequest $req, $id)
    {
        $dto = new RepositoryDTO($req->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->repositoryService->update($id, $dto);
        $this->authorize('update', $result);
        return ApiResponse::success(new RepositoryResource($result), 'Berhasil memperbarui data repository');
    }

    public function delete($id)
    {
        $model = ContentDetailRepository::findOrFail($id);
        $this->authorize('delete', $model);
        $this->repositoryService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus repository');
    }
}

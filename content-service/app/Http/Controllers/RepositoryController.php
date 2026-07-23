<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateRepositoryRequest;
use App\Http\Requests\UpdateRepositoryRequest;
use App\Http\Resources\RepositoryResource;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\RepositoryDTO;
use App\Services\RepositoryService;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    public function __construct(protected RepositoryService $repositoryService) {}

    public function sideMenu()
    {
        try {
            return ApiResponse::success($this->repositoryService->sideMenu(), 'Berhasil mengambil side menu untuk tipe repositori');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(Request $request, $contentId)
    {
        try {
            return ApiResponse::success($this->repositoryService->index(QueryFilterDTO::fromRequest($request), $contentId), 'Berhasil mengambil data repositori');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(CreateRepositoryRequest $req)
    {
        try {
            $dto = new RepositoryDTO($req->validated());
            $dto->created_by = $dto->updated_by = auth()->user()->name;

            return ApiResponse::success(new RepositoryResource($this->repositoryService->create($dto)), 'Berhasil menambahkan data repository', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            return ApiResponse::success(new RepositoryResource($this->repositoryService->detail($id)), 'Berhasil mengambil data detail repository');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function update(UpdateRepositoryRequest $req, $id)
    {
        try {
            $dto = new RepositoryDTO($req->validated());
            $dto->updated_by = auth()->user()->name;

            return ApiResponse::success(new RepositoryResource($this->repositoryService->update($id, $dto)), 'Berhasil memperbarui data repository');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete($id)
    {
        try {
            $this->repositoryService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus repository');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

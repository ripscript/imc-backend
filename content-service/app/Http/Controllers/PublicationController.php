<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreatePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Http\Resources\PublicationResource;
use App\Services\DTOs\PublicationDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\PublicationService;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    public function __construct(protected PublicationService $publicationService) {}

    public function sideMenu()
    {
        try {
            return ApiResponse::success($this->publicationService->sideMenu(), 'Berhasil mengambil side menu untuk tipe publikasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(Request $request, $contentId)
    {
        try {
            return ApiResponse::success($this->publicationService->index(QueryFilterDTO::fromRequest($request), $contentId), 'Berhasil mengambil data publikasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(CreatePublicationRequest $req)
    {
        try {
            $dto = new PublicationDTO($req->validated());
            $dto->created_by = $dto->updated_by = auth()->user()->name;

            return ApiResponse::success(new PublicationResource($this->publicationService->create($dto)), 'Berhasil menambahkan data publikasi', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            return ApiResponse::success(new PublicationResource($this->publicationService->detail($id)), 'Berhasil mengambil data detail publikasi');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function update(UpdatePublicationRequest $req, $id)
    {
        try {
            $dto = new PublicationDTO($req->validated());
            $dto->updated_by = auth()->user()->name;

            return ApiResponse::success(new PublicationResource($this->publicationService->update($id, $dto)), 'Berhasil memperbarui data publikasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete($id)
    {
        try {
            $this->publicationService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus publikasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

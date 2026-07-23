<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateGalleryRequest;
use App\Http\Requests\UpdateGalleryRequest;
use App\Http\Resources\GalleryResource;
use App\Services\DTOs\GalleryDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\GalleryService;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function __construct(
        protected GalleryService $galleryService
    ) {}

    public function sideMenu()
    {
        try {
            $data = $this->galleryService->sideMenu();

            return ApiResponse::success($data, 'Berhasil mengambil side menu untuk tipe galeri');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(Request $request, $contentId)
    {
        try {
            $data = $this->galleryService->index(QueryFilterDTO::fromRequest($request), $contentId);

            return ApiResponse::success($data, 'Berhasil mengambil data galeri');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(CreateGalleryRequest $request)
    {
        try {
            $dto = new GalleryDTO($request->validated());
            $dto->created_by = auth()->user()->name;
            $dto->updated_by = auth()->user()->name;
            $result = $this->galleryService->create($dto);

            return ApiResponse::success(new GalleryResource($result), 'Berhasil menambahkan data galeri', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            return ApiResponse::success(new GalleryResource($this->galleryService->detail($id)), 'Berhasil mengambil data detail galeri');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function update(UpdateGalleryRequest $request, $id)
    {
        try {
            $dto = new GalleryDTO($request->validated());
            $dto->updated_by = auth()->user()->name;

            return ApiResponse::success(new GalleryResource($this->galleryService->update($id, $dto)), 'Berhasil memperbarui data galeri');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete($id)
    {
        try {
            $this->galleryService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus galeri');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

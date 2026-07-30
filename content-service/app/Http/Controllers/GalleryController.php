<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateGalleryRequest;
use App\Http\Requests\UpdateGalleryRequest;
use App\Http\Resources\GalleryResource;
use App\Services\DTOs\GalleryDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\GalleryService;
use App\Models\ContentDetailGallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function __construct(
        protected GalleryService $galleryService
    ) {}

    public function sideMenu()
    {
        $this->authorize('viewAny', ContentDetailGallery::class);
        $data = $this->galleryService->sideMenu();

        return ApiResponse::success($data, 'Berhasil mengambil side menu untuk tipe galeri');
    }

    public function index(Request $request, $contentId)
    {
        $this->authorize('viewAny', ContentDetailGallery::class);
        $data = $this->galleryService->index(QueryFilterDTO::fromRequest($request), $contentId);

        return ApiResponse::success($data, 'Berhasil mengambil data galeri');
    }

    public function create(CreateGalleryRequest $request)
    {
        $dto = new GalleryDTO($request->validated());
        $dto->created_by = auth()->user()->name;
        $dto->updated_by = auth()->user()->name;
        $this->authorize('create', ContentDetailGallery::class);

        $result = $this->galleryService->create($dto);

        return ApiResponse::success(new GalleryResource($result), 'Berhasil menambahkan data galeri', 201);
    }

    public function detail($id)
    {
        $result = $this->galleryService->detail($id);
        $this->authorize('view', $result);
        return ApiResponse::success(new GalleryResource($result), 'Berhasil mengambil data detail galeri');
    }

    public function update(UpdateGalleryRequest $request, $id)
    {
        $dto = new GalleryDTO($request->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->galleryService->update($id, $dto);
        $this->authorize('update', $result);
        return ApiResponse::success(new GalleryResource($result), 'Berhasil memperbarui data galeri');
    }

    public function delete($id)
    {
        $this->authorize('delete', ContentDetailGallery::class);
        $this->galleryService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus galeri');
    }
}

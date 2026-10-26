<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateKategoriContentRequest;
use App\Http\Requests\UpdateKategoriContentRequest;
use App\Http\Resources\KategoriContentResource;
use App\Models\MsKategoriContent;
use App\Services\DTOs\KategoriContentDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\KategoriContentService;
use Illuminate\Http\Request;

class KategoriContentController extends Controller
{
    public function __construct(
        protected KategoriContentService $kategoriContentService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', MsKategoriContent::class);

        $data = $this->kategoriContentService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data kategori konten');
    }

    public function detail($id)
    {
        $kategori = $this->kategoriContentService->detail($id);
        $this->authorize('view', $kategori);

        return ApiResponse::success(new KategoriContentResource($kategori), 'Berhasil mengambil data detail kategori konten');
    }

    public function create(CreateKategoriContentRequest $request)
    {
        $this->authorize('create', MsKategoriContent::class);

        $dto = new KategoriContentDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->kategoriContentService->create($dto);

        return ApiResponse::success(new KategoriContentResource($result), 'Berhasil menambahkan data kategori konten', 201);
    }

    public function update(UpdateKategoriContentRequest $request, $id)
    {
        $dto = new KategoriContentDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->kategoriContentService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new KategoriContentResource($result), 'Berhasil memperbarui data kategori konten');
    }

    public function destroy($id)
    {
        $this->authorize('delete', MsKategoriContent::class);

        $this->kategoriContentService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data kategori konten');
    }
}

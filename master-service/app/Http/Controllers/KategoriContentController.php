<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateKategoriContentRequest;
use App\Http\Requests\UpdateKategoriContentRequest;
use App\Http\Resources\KategoriContentResource;
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
        try {
            $data = $this->kategoriContentService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data kategori konten');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $kategori = $this->kategoriContentService->detail($id);

            return ApiResponse::success(new KategoriContentResource($kategori), 'Berhasil mengambil data detail kategori konten');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateKategoriContentRequest $request)
    {
        try {
            $dto = new KategoriContentDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->kategoriContentService->create($dto);

            return ApiResponse::success(new KategoriContentResource($result), 'Berhasil menambahkan data kategori konten', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateKategoriContentRequest $request, $id)
    {
        try {
            $dto = new KategoriContentDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->kategoriContentService->update($id, $dto);

            return ApiResponse::success(new KategoriContentResource($result), 'Berhasil memperbarui data kategori konten');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->kategoriContentService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data kategori konten');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

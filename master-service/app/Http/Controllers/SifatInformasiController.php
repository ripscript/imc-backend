<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateSifatInformasiRequest;
use App\Http\Requests\UpdateSifatInformasiRequest;
use App\Http\Resources\SifatInformasiResource;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\SifatInformasiDTO;
use App\Services\SifatInformasiService;
use Illuminate\Http\Request;

class SifatInformasiController extends Controller
{
    public function __construct(
        protected SifatInformasiService $sifatInformasiService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->sifatInformasiService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data sifat informasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $sifatInformasi = $this->sifatInformasiService->detail($id);

            return ApiResponse::success(new SifatInformasiResource($sifatInformasi), 'Berhasil mengambil data detail sifat informasi');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateSifatInformasiRequest $request)
    {
        try {
            $dto = new SifatInformasiDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->sifatInformasiService->create($dto);

            return ApiResponse::success(new SifatInformasiResource($result), 'Berhasil menambahkan data sifat informasi', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateSifatInformasiRequest $request, $id)
    {
        try {
            $dto = new SifatInformasiDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->sifatInformasiService->update($id, $dto);

            return ApiResponse::success(new SifatInformasiResource($result), 'Berhasil memperbarui data sifat informasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->sifatInformasiService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data sifat informasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

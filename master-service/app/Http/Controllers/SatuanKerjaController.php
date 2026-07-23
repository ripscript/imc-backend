<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateSatuanKerjaRequest;
use App\Http\Requests\UpdateSatuanKerjaRequest;
use App\Http\Resources\SatuanKerjaResource;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\SatuanKerjaDTO;
use App\Services\SatuanKerjaService;
use Illuminate\Http\Request;

class SatuanKerjaController extends Controller
{
    public function __construct(
        protected SatuanKerjaService $satuanKerjaService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->satuanKerjaService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data satuan kerja');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $satuanKerja = $this->satuanKerjaService->detail($id);

            return ApiResponse::success(new SatuanKerjaResource($satuanKerja), 'Berhasil mengambil data detail satuan kerja');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateSatuanKerjaRequest $request)
    {
        try {
            $dto = new SatuanKerjaDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->satuanKerjaService->create($dto);

            return ApiResponse::success(new SatuanKerjaResource($result), 'Berhasil menambahkan data satuan kerja', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateSatuanKerjaRequest $request, $id)
    {
        try {
            $dto = new SatuanKerjaDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->satuanKerjaService->update($id, $dto);

            return ApiResponse::success(new SatuanKerjaResource($result), 'Berhasil memperbarui data satuan kerja');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->satuanKerjaService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data satuan kerja');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

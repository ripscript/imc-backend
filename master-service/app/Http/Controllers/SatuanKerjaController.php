<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateSatuanKerjaRequest;
use App\Http\Requests\UpdateSatuanKerjaRequest;
use App\Http\Resources\SatuanKerjaResource;
use App\Models\MsSatuanKerja;
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
        $this->authorize('viewAny', MsSatuanKerja::class);

        $data = $this->satuanKerjaService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data satuan kerja');
    }

    public function detail($id)
    {
        $satuanKerja = $this->satuanKerjaService->detail($id);
        $this->authorize('view', $satuanKerja);

        return ApiResponse::success(new SatuanKerjaResource($satuanKerja), 'Berhasil mengambil data detail satuan kerja');
    }

    public function create(CreateSatuanKerjaRequest $request)
    {
        $this->authorize('create', MsSatuanKerja::class);

        $dto = new SatuanKerjaDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->satuanKerjaService->create($dto);

        return ApiResponse::success(new SatuanKerjaResource($result), 'Berhasil menambahkan data satuan kerja', 201);
    }

    public function update(UpdateSatuanKerjaRequest $request, $id)
    {
        $dto = new SatuanKerjaDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->satuanKerjaService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new SatuanKerjaResource($result), 'Berhasil memperbarui data satuan kerja');
    }

    public function destroy($id)
    {
        $this->authorize('delete', MsSatuanKerja::class);

        $this->satuanKerjaService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data satuan kerja');
    }
}

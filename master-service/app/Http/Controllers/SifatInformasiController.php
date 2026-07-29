<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateSifatInformasiRequest;
use App\Http\Requests\UpdateSifatInformasiRequest;
use App\Http\Resources\SifatInformasiResource;
use App\Models\MsSifatInformasi;
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
        $this->authorize('viewAny', MsSifatInformasi::class);

        $data = $this->sifatInformasiService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data sifat informasi');
    }

    public function detail($id)
    {
        $sifatInformasi = $this->sifatInformasiService->detail($id);
        $this->authorize('view', $sifatInformasi);

        return ApiResponse::success(new SifatInformasiResource($sifatInformasi), 'Berhasil mengambil data detail sifat informasi');
    }

    public function create(CreateSifatInformasiRequest $request)
    {
        $this->authorize('create', MsSifatInformasi::class);

        $dto = new SifatInformasiDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->sifatInformasiService->create($dto);

        return ApiResponse::success(new SifatInformasiResource($result), 'Berhasil menambahkan data sifat informasi', 201);
    }

    public function update(UpdateSifatInformasiRequest $request, $id)
    {
        $dto = new SifatInformasiDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->sifatInformasiService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new SifatInformasiResource($result), 'Berhasil memperbarui data sifat informasi');
    }

    public function destroy($id)
    {
        $this->authorize('delete', MsSifatInformasi::class);

        $this->sifatInformasiService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data sifat informasi');
    }
}

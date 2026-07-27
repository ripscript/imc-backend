<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateAlasanKeberatanRequest;
use App\Http\Requests\UpdateAlasanKeberatanRequest;
use App\Http\Resources\AlasanKeberatanResource;
use App\Models\MsAlasanKeberatan;
use App\Services\AlasanKeberatanService;
use App\Services\DTOs\AlasanKeberatanDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Http\Request;

class AlasanKeberatanController extends Controller
{
    public function __construct(
        protected AlasanKeberatanService $alasanKeberatanService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', MsAlasanKeberatan::class);

        $data = $this->alasanKeberatanService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data alasan keberatan');
    }

    public function detail($id)
    {
        $alasan = $this->alasanKeberatanService->detail($id);
        $this->authorize('view', $alasan);

        return ApiResponse::success(new AlasanKeberatanResource($alasan), 'Berhasil mengambil data alasan keberatan');
    }

    public function create(CreateAlasanKeberatanRequest $request)
    {
        $this->authorize('create', MsAlasanKeberatan::class);

        $dto = new AlasanKeberatanDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->alasanKeberatanService->create($dto);

        return ApiResponse::success(new AlasanKeberatanResource($result), 'Berhasil menambahkan data alasan keberatan', 201);
    }

    public function update(UpdateAlasanKeberatanRequest $request, $id)
    {
        $dto = new AlasanKeberatanDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->alasanKeberatanService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new AlasanKeberatanResource($result), 'Berhasil memperbarui data alasan keberatan');
    }

    public function destroy($id)
    {
        $this->authorize('delete', MsAlasanKeberatan::class);

        $this->alasanKeberatanService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data alasan keberatan');
    }
}

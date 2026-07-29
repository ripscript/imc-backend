<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateNegaraRequest;
use App\Http\Requests\UpdateNegaraRequest;
use App\Http\Resources\NegaraResource;
use App\Models\MsNegara;
use App\Services\DTOs\NegaraDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\NegaraService;
use Illuminate\Http\Request;

class NegaraController extends Controller
{
    public function __construct(
        protected NegaraService $negaraService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', MsNegara::class);

        $data = $this->negaraService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data negara');
    }

    public function detail($id)
    {
        $negara = $this->negaraService->detail($id);
        $this->authorize('view', $negara);

        return ApiResponse::success(new NegaraResource($negara), 'Berhasil mengambil data detail negara');
    }

    public function create(CreateNegaraRequest $request)
    {
        $this->authorize('create', MsNegara::class);

        $dto = new NegaraDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->negaraService->create($dto);

        return ApiResponse::success(new NegaraResource($result), 'Berhasil menambahkan data negara', 201);
    }

    public function update(UpdateNegaraRequest $request, $id)
    {
        $dto = new NegaraDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->negaraService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new NegaraResource($result), 'Berhasil memperbarui data negara');
    }

    public function destroy($id)
    {
        $this->authorize('delete', MsNegara::class);

        $this->negaraService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data negara');
    }
}

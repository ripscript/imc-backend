<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateNegaraRequest;
use App\Http\Requests\UpdateNegaraRequest;
use App\Http\Resources\NegaraResource;
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
        try {
            $data = $this->negaraService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data negara');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $negara = $this->negaraService->detail($id);

            return ApiResponse::success(new NegaraResource($negara), 'Berhasil mengambil data detail negara');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateNegaraRequest $request)
    {
        try {
            $dto = new NegaraDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->negaraService->create($dto);

            return ApiResponse::success(new NegaraResource($result), 'Berhasil menambahkan data negara', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateNegaraRequest $request, $id)
    {
        try {
            $dto = new NegaraDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->negaraService->update($id, $dto);

            return ApiResponse::success(new NegaraResource($result), 'Berhasil memperbarui data negara');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->negaraService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data negara');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

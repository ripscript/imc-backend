<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreatePerwakilanRequest;
use App\Http\Requests\UpdatePerwakilanRequest;
use App\Http\Resources\PerwakilanResource;
use App\Services\DTOs\PerwakilanDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\PerwakilanService;
use Illuminate\Http\Request;

class PerwakilanController extends Controller
{
    public function __construct(
        protected PerwakilanService $perwakilanService
    ) {}

    public function index(Request $request)
    {
        try {
            $result = $this->perwakilanService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($result, 'Berhasil mengambil data perwakilan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $perwakilan = $this->perwakilanService->detail($id);

            return ApiResponse::success(new PerwakilanResource($perwakilan), 'Berhasil mengambil detail perwakilan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(CreatePerwakilanRequest $request)
    {
        try {
            $dto = new PerwakilanDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->perwakilanService->create($dto);

            if (is_array($result) && isset($result['error'])) {
                return ApiResponse::error($result['error'], '', $result['code']);
            }

            return ApiResponse::success(new PerwakilanResource($result), 'Berhasil menambahkan data perwakilan', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdatePerwakilanRequest $request, $id)
    {
        try {
            $dto = new PerwakilanDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->perwakilanService->update($id, $dto);

            if (is_array($result) && isset($result['error'])) {
                return ApiResponse::error($result['error'], '', $result['code']);
            }

            return ApiResponse::success(new PerwakilanResource($result), 'Berhasil memperbarui data perwakilan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->perwakilanService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data perwakilan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

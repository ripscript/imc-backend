<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateAlasanKeberatanRequest;
use App\Http\Requests\UpdateAlasanKeberatanRequest;
use App\Http\Resources\AlasanKeberatanResource;
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
        try {
            $data = $this->alasanKeberatanService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data alasan keberatan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $alasan = $this->alasanKeberatanService->detail($id);

            return ApiResponse::success(new AlasanKeberatanResource($alasan), 'Berhasil mengambil data alasan keberatan');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateAlasanKeberatanRequest $request)
    {
        try {
            $dto = new AlasanKeberatanDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->alasanKeberatanService->create($dto);

            return ApiResponse::success(new AlasanKeberatanResource($result), 'Berhasil menambahkan data alasan keberatan', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateAlasanKeberatanRequest $request, $id)
    {
        try {
            $dto = new AlasanKeberatanDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->alasanKeberatanService->update($id, $dto);

            return ApiResponse::success(new AlasanKeberatanResource($result), 'Berhasil memperbarui data alasan keberatan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->alasanKeberatanService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data alasan keberatan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

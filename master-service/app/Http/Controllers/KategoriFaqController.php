<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateKategoriFaqRequest;
use App\Http\Requests\UpdateKategoriFaqRequest;
use App\Http\Resources\KategoriFaqResource;
use App\Services\DTOs\KategoriFaqDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\KategoriFaqService;
use Illuminate\Http\Request;

class KategoriFaqController extends Controller
{
    public function __construct(
        protected KategoriFaqService $kategoriFaqService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->kategoriFaqService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data kategori FAQ');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $kategoriFaq = $this->kategoriFaqService->detail($id);

            return ApiResponse::success(new KategoriFaqResource($kategoriFaq), 'Berhasil mengambil data detail kategori FAQ');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateKategoriFaqRequest $request)
    {
        try {
            $dto = new KategoriFaqDTO($request->validated()['name']);
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->kategoriFaqService->create($dto);

            return ApiResponse::success(new KategoriFaqResource($result), 'Berhasil menambahkan data kategori FAQ', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateKategoriFaqRequest $request, $id)
    {
        try {
            $dto = new KategoriFaqDTO($request->validated()['name']);
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->kategoriFaqService->update($id, $dto);

            return ApiResponse::success(new KategoriFaqResource($result), 'Berhasil memperbarui data kategori FAQ');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->kategoriFaqService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data kategori FAQ');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

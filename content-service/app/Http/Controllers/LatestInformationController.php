<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateLatestInformationRequest;
use App\Http\Requests\UpdateLatestInformationRequest;
use App\Http\Resources\LatestInformationResource;
use App\Services\DTOs\LatestInformationDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\LatestInformationService;
use Illuminate\Http\Request;

class LatestInformationController extends Controller
{
    public function __construct(
        protected LatestInformationService $latestInformationService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->latestInformationService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data informasi terkini');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $informasi = $this->latestInformationService->detail($id);

            return ApiResponse::success(new LatestInformationResource($informasi), 'Berhasil mengambil data informasi terkini');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateLatestInformationRequest $request)
    {
        try {
            $dto = new LatestInformationDTO($request->validated());
            $dto->created_by = auth()->user()->name;
            $dto->updated_by = auth()->user()->name;

            $result = $this->latestInformationService->create($dto);

            return ApiResponse::success(new LatestInformationResource($result), 'Berhasil menambahkan data informasi terkini', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateLatestInformationRequest $request, $id)
    {
        try {
            $dto = new LatestInformationDTO($request->validated());
            $dto->updated_by = auth()->user()->name;

            $result = $this->latestInformationService->update($id, $dto);

            return ApiResponse::success(new LatestInformationResource($result), 'Berhasil memperbarui data informasi terkini');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete($id)
    {
        try {
            $this->latestInformationService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data informasi terkini');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

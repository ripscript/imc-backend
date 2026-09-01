<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateLatestInformationRequest;
use App\Http\Requests\UpdateLatestInformationRequest;
use App\Http\Resources\LatestInformationResource;
use App\Services\DTOs\LatestInformationDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\LatestInformationService;
use App\Models\LatestInformation;
use Illuminate\Http\Request;

class LatestInformationController extends Controller
{
    public function __construct(
        protected LatestInformationService $latestInformationService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', LatestInformation::class);
        $data = $this->latestInformationService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data informasi terkini');
    }

    public function detail($id)
    {
        $result = $this->latestInformationService->detail($id);
        $this->authorize('view', $result);

        return ApiResponse::success(new LatestInformationResource($result), 'Berhasil mengambil data informasi terkini');
    }

    public function create(CreateLatestInformationRequest $request)
    {
        $dto = new LatestInformationDTO($request->validated());
        $dto->created_by = auth()->user()->name;
        $dto->updated_by = auth()->user()->name;
        $this->authorize('create', LatestInformation::class);

        $result = $this->latestInformationService->create($dto);

        return ApiResponse::success(new LatestInformationResource($result), 'Berhasil menambahkan data informasi terkini', 201);
    }

    public function update(UpdateLatestInformationRequest $request, $id)
    {
        $dto = new LatestInformationDTO($request->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->latestInformationService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new LatestInformationResource($result), 'Berhasil memperbarui data informasi terkini');
    }

    public function delete($id)
    {
        $model = LatestInformation::findOrFail($id);
        $this->authorize('delete', $model);
        $this->latestInformationService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data informasi terkini');
    }
}

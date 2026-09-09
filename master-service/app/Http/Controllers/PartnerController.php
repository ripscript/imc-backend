<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreatePartnerRequest;
use App\Http\Requests\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\PmPartner;
use App\Services\DTOs\PartnerDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\ProjectManagement\PartnerService;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function __construct(
        protected PartnerService $partnerService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PmPartner::class);

        $data = $this->partnerService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data mitra');
    }

    public function detail($id)
    {
        $partner = $this->partnerService->detail($id);
        $this->authorize('view', $partner);

        return ApiResponse::success(new PartnerResource($partner), 'Berhasil mengambil data detail mitra');
    }

    public function create(CreatePartnerRequest $request)
    {
        $this->authorize('create', PmPartner::class);

        $dto = new PartnerDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->partnerService->create($dto);

        return ApiResponse::success(new PartnerResource($result), 'Mitra dibuat', 201);
    }

    public function update(UpdatePartnerRequest $request, $id)
    {
        $dto = new PartnerDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->partnerService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new PartnerResource($result), 'Mitra diperbarui');
    }

    public function destroy($id)
    {
        $this->authorize('delete', PmPartner::class);

        $this->partnerService->destroy($id);

        return ApiResponse::success('', 'Mitra dihapus');
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateCustomFieldRequest;
use App\Http\Requests\UpdateCustomFieldRequest;
use App\Http\Requests\UpsertCustomFieldValueRequest;
use App\Http\Resources\CustomFieldResource;
use App\Models\PmCustomField;
use App\Services\DTOs\CustomFieldDTO;
use App\Services\DTOs\CustomFieldValueDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\ProjectManagement\CustomFieldService;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function __construct(
        protected CustomFieldService $customFieldService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PmCustomField::class);

        $data = $this->customFieldService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data custom field');
    }

    public function detail($id)
    {
        $field = $this->customFieldService->detail($id);
        $this->authorize('view', $field);

        return ApiResponse::success(new CustomFieldResource($field), 'Berhasil mengambil data detail custom field');
    }

    public function create(CreateCustomFieldRequest $request)
    {
        $this->authorize('create', PmCustomField::class);

        $dto = new CustomFieldDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->customFieldService->create($dto);

        return ApiResponse::success(new CustomFieldResource($result), 'Custom field dibuat', 201);
    }

    public function update(UpdateCustomFieldRequest $request, $id)
    {
        $dto = new CustomFieldDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->customFieldService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new CustomFieldResource($result), 'Custom field diperbarui');
    }

    public function destroy($id)
    {
        $this->authorize('delete', PmCustomField::class);

        $this->customFieldService->destroy($id);

        return ApiResponse::success('', 'Custom field dihapus');
    }

    public function values(Request $request)
    {
        $this->authorize('viewAny', PmCustomField::class);

        $data = $this->customFieldService->getValues(
            $request->input('entity_type'),
            $request->input('entity_id')
        );

        return ApiResponse::success($data, 'Berhasil mengambil nilai custom field');
    }

    public function upsertValues(UpsertCustomFieldValueRequest $request)
    {
        $this->authorize('create', PmCustomField::class);

        $dto = new CustomFieldValueDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $this->customFieldService->upsertValues($dto);

        return ApiResponse::success('', 'Nilai custom field disimpan');
    }
}

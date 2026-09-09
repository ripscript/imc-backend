<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateDocumentTypeRequest;
use App\Http\Requests\UpdateDocumentTypeRequest;
use App\Http\Resources\DocumentTypeResource;
use App\Models\PmDocumentType;
use App\Services\DTOs\DocumentTypeDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\ProjectManagement\DocumentTypeService;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function __construct(
        protected DocumentTypeService $documentTypeService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PmDocumentType::class);

        $data = $this->documentTypeService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data tipe dokumen');
    }

    public function detail($id)
    {
        $documentType = $this->documentTypeService->detail($id);
        $this->authorize('view', $documentType);

        return ApiResponse::success(new DocumentTypeResource($documentType), 'Berhasil mengambil data detail tipe dokumen');
    }

    public function create(CreateDocumentTypeRequest $request)
    {
        $this->authorize('create', PmDocumentType::class);

        $dto = new DocumentTypeDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->documentTypeService->create($dto);

        return ApiResponse::success(new DocumentTypeResource($result), 'Tipe dokumen dibuat', 201);
    }

    public function update(UpdateDocumentTypeRequest $request, $id)
    {
        $dto = new DocumentTypeDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->documentTypeService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new DocumentTypeResource($result), 'Tipe dokumen diperbarui');
    }

    public function destroy($id)
    {
        $this->authorize('delete', PmDocumentType::class);

        $this->documentTypeService->destroy($id);

        return ApiResponse::success('', 'Tipe dokumen dihapus');
    }
}

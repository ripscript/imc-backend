<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Resources\PmDocumentResource;
use App\Services\DTOs\PmDocumentDTO;
use App\Services\PmDocumentService;

class StageDocumentController extends Controller
{
    public function __construct(
        protected PmDocumentService $documentService
    ) {}

    public function index($project_id, $stage_id)
    {
        $documents = $this->documentService->list((int) $project_id, (int) $stage_id);

        return ApiResponse::success(
            PmDocumentResource::collection($documents),
            'Berhasil mengambil data dokumen'
        );
    }

    public function store(StoreDocumentRequest $request, $project_id, $stage_id)
    {
        $user = auth('admin')->user();

        $dto = new PmDocumentDTO($request->validated());
        $dto->created_by = $user?->name;
        $dto->updated_by = $user?->name;

        $document = $this->documentService->create((int) $project_id, (int) $stage_id, $dto, $user?->id);

        return ApiResponse::success(new PmDocumentResource($document), 'Dokumen diunggah', 201);
    }

    public function destroy($project_id, $stage_id, $document_id)
    {
        $this->documentService->delete((int) $project_id, (int) $stage_id, (int) $document_id);

        return ApiResponse::success('', 'Dokumen dihapus');
    }
}

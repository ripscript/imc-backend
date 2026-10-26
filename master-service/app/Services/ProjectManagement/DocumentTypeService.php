<?php

namespace App\Services\ProjectManagement;

use App\Models\PmDocumentType;
use App\Services\DTOs\DocumentTypeDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\QueryService;
use Illuminate\Support\Facades\DB;

class DocumentTypeService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = PmDocumentType::query();

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name', 'code']);
    }

    public function detail($id)
    {
        return PmDocumentType::findOrFail($id);
    }

    public function create(DocumentTypeDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $documentType = new PmDocumentType;
            $documentType->name = $dto->name;
            $documentType->code = $dto->code;
            $documentType->allowed_extension = $dto->allowed_extension;
            $documentType->max_size_mb = $dto->max_size_mb ?? 10;
            $documentType->status = $dto->status ?? 'active';
            $documentType->created_by = $dto->created_by;
            $documentType->updated_by = $dto->updated_by;
            $documentType->save();

            if (empty($documentType->code)) {
                $documentType->code = ProjectCodeGenerator::documentType($documentType->id);
                $documentType->save();
            }

            return $documentType;
        });
    }

    public function update($id, DocumentTypeDTO $dto)
    {
        $documentType = PmDocumentType::findOrFail($id);

        if ($dto->name !== null) {
            $documentType->name = $dto->name;
        }
        if ($dto->allowed_extension !== null) {
            $documentType->allowed_extension = $dto->allowed_extension;
        }
        if ($dto->max_size_mb !== null) {
            $documentType->max_size_mb = $dto->max_size_mb;
        }
        if ($dto->status !== null) {
            $documentType->status = $dto->status;
        }
        $documentType->updated_by = $dto->updated_by;
        $documentType->save();

        return $documentType;
    }

    public function destroy($id)
    {
        PmDocumentType::findOrFail($id)->delete();

        return true;
    }
}

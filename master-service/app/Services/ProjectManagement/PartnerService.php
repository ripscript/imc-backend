<?php

namespace App\Services\ProjectManagement;

use App\Models\PmPartner;
use App\Services\DTOs\PartnerDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\QueryService;
use Illuminate\Support\Facades\DB;

class PartnerService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = PmPartner::query();

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name', 'identifier']);
    }

    public function detail($id)
    {
        return PmPartner::findOrFail($id);
    }

    public function create(PartnerDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $partner = new PmPartner;
            $partner->name = $dto->name;
            $partner->identifier = $dto->identifier;
            $partner->status = $dto->status ?? 'active';
            $partner->created_by = $dto->created_by;
            $partner->updated_by = $dto->updated_by;
            $partner->save();

            if (empty($partner->identifier)) {
                $partner->identifier = ProjectCodeGenerator::partner($partner->id);
                $partner->save();
            }

            return $partner;
        });
    }

    public function update($id, PartnerDTO $dto)
    {
        $partner = PmPartner::findOrFail($id);

        if ($dto->name !== null) {
            $partner->name = $dto->name;
        }
        if ($dto->status !== null) {
            $partner->status = $dto->status;
        }
        $partner->updated_by = $dto->updated_by;
        $partner->save();

        return $partner;
    }

    public function destroy($id)
    {
        PmPartner::findOrFail($id)->delete();

        return true;
    }
}

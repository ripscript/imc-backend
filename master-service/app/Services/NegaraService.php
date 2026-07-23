<?php

namespace App\Services;

use App\Models\MsNegara;
use App\Services\DTOs\NegaraDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class NegaraService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_negara')
            ->whereNull('deleted_at')
            ->select('id', 'name');

        if ($filter->without_pagination) {
            return $query->get();
        }

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name']);
    }

    public function detail($id)
    {
        return MsNegara::findOrFail($id);
    }

    public function create(NegaraDTO $dto)
    {
        $negara = new MsNegara;
        $negara->name = $dto->name;
        $negara->created_by = $dto->created_by;
        $negara->updated_by = $dto->updated_by;
        $negara->save();

        return $negara;
    }

    public function update($id, NegaraDTO $dto)
    {
        $negara = MsNegara::findOrFail($id);
        $negara->name = $dto->name;
        $negara->updated_by = $dto->updated_by;
        $negara->save();

        return $negara;
    }

    public function destroy($id)
    {
        MsNegara::findOrFail($id)->delete();

        return true;
    }
}

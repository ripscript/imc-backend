<?php

namespace App\Services;

use App\Models\MsAlasanKeberatan;
use App\Services\DTOs\AlasanKeberatanDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class AlasanKeberatanService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_alasan_keberatan')
            ->whereNull('deleted_at')
            ->select('id', 'name', 'is_active');

        if ($filter->is_active !== null) {
            $query->where('is_active', $filter->is_active);
        }

        if ($filter->without_pagination) {
            return $query->get();
        }

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name']);
    }

    public function detail($id)
    {
        return MsAlasanKeberatan::findOrFail($id);
    }

    public function create(AlasanKeberatanDTO $dto)
    {
        $alasan = new MsAlasanKeberatan;
        $alasan->name = $dto->name;
        $alasan->is_active = $dto->is_active;
        $alasan->created_by = $dto->created_by;
        $alasan->updated_by = $dto->updated_by;
        $alasan->save();

        return $alasan;
    }

    public function update($id, AlasanKeberatanDTO $dto)
    {
        $alasan = MsAlasanKeberatan::findOrFail($id);
        $alasan->name = $dto->name;
        $alasan->is_active = $dto->is_active;
        $alasan->updated_by = $dto->updated_by;
        $alasan->save();

        return $alasan;
    }

    public function destroy($id)
    {
        MsAlasanKeberatan::findOrFail($id)->delete();

        return true;
    }
}

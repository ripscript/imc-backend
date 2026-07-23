<?php

namespace App\Services;

use App\Models\MsKategoriFaq;
use App\Services\DTOs\KategoriFaqDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class KategoriFaqService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_kategori_faq')
            ->whereNull('deleted_at')
            ->select('id', 'name');

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name']);
    }

    public function detail($id)
    {
        return MsKategoriFaq::findOrFail($id);
    }

    public function create(KategoriFaqDTO $dto)
    {
        $kategoriFaq = new MsKategoriFaq;
        $kategoriFaq->name = $dto->name;
        $kategoriFaq->created_by = $dto->created_by;
        $kategoriFaq->updated_by = $dto->updated_by;
        $kategoriFaq->save();

        return $kategoriFaq;
    }

    public function update($id, KategoriFaqDTO $dto)
    {
        $kategoriFaq = MsKategoriFaq::findOrFail($id);
        $kategoriFaq->name = $dto->name;
        $kategoriFaq->updated_by = $dto->updated_by;
        $kategoriFaq->save();

        return $kategoriFaq;
    }

    public function destroy($id)
    {
        MsKategoriFaq::findOrFail($id)->delete();

        return true;
    }
}

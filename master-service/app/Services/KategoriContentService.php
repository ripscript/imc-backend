<?php

namespace App\Services;

use App\Models\MsKategoriContent;
use App\Services\DTOs\KategoriContentDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class KategoriContentService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_kategori_contents')->select('id', 'title', 'is_active');

        if ($filter->is_active !== null) {
            $query->where('is_active', $filter->is_active);
        }

        if ($filter->without_pagination) {
            return $query->get();
        }

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['title']);
    }

    public function detail($id)
    {
        return MsKategoriContent::findOrFail($id);
    }

    public function create(KategoriContentDTO $dto)
    {
        $kategori = new MsKategoriContent;
        $kategori->title = $dto->title;
        $kategori->is_active = $dto->is_active;
        $kategori->created_by = $dto->created_by;
        $kategori->updated_by = $dto->updated_by;
        $kategori->save();

        return $kategori;
    }

    public function update($id, KategoriContentDTO $dto)
    {
        $kategori = MsKategoriContent::findOrFail($id);
        $kategori->title = $dto->title;
        $kategori->is_active = $dto->is_active;
        $kategori->updated_by = $dto->updated_by;
        $kategori->save();

        return $kategori;
    }

    public function destroy($id)
    {
        MsKategoriContent::findOrFail($id)->delete();

        return true;
    }
}

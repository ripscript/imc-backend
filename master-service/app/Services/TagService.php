<?php

namespace App\Services;

use App\Models\MsTag;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\TagDTO;
use Illuminate\Support\Facades\DB;

class TagService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_tags')->select('id', 'title', 'is_active');

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
        return MsTag::findOrFail($id);
    }

    public function create(TagDTO $dto)
    {
        $tag = new MsTag;
        $tag->title = $dto->title;
        $tag->is_active = $dto->is_active;
        $tag->created_by = $dto->created_by;
        $tag->updated_by = $dto->updated_by;
        $tag->save();

        return $tag;
    }

    public function update($id, TagDTO $dto)
    {
        $tag = MsTag::findOrFail($id);
        $tag->title = $dto->title;
        $tag->is_active = $dto->is_active;
        $tag->updated_by = $dto->updated_by;
        $tag->save();

        return $tag;
    }

    public function destroy($id)
    {
        MsTag::findOrFail($id)->delete();

        return true;
    }
}

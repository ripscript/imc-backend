<?php

namespace App\Services;

use App\Models\QuickLink;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\QuickLinkDTO;
use Illuminate\Support\Facades\DB;

class QuickLinkService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('quick_links')
            ->select('id', 'title', 'url', 'order', 'is_active');

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['title', 'url']);
    }

    public function detail($id)
    {
        return QuickLink::findOrFail($id);
    }

    public function create(QuickLinkDTO $dto)
    {
        $link = new QuickLink;
        $link->title = $dto->title;
        $link->url = $dto->url;
        $link->order = $dto->order ?? 0;
        $link->is_active = $dto->is_active;
        $link->created_by = $dto->created_by;
        $link->updated_by = $dto->updated_by;
        $link->save();

        return $link;
    }

    public function update($id, QuickLinkDTO $dto)
    {
        $link = QuickLink::findOrFail($id);
        $link->title = $dto->title;
        $link->url = $dto->url;
        if ($dto->order !== null) {
            $link->order = $dto->order;
        }
        $link->is_active = $dto->is_active;
        $link->updated_by = $dto->updated_by;
        $link->save();

        return $link;
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            QuickLink::findOrFail($id)->delete();
            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

<?php

namespace App\Services;

use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class QueryService
{
    public function applyFiltersAndPaginate(EloquentBuilder|QueryBuilder $query, QueryFilterDTO $filter, array $searchable = [], $defaultSort = 'updated_at')
    {
        $query = $this->search($query, $filter, $searchable);
        $query = $this->sort($query, $filter, $defaultSort);

        return $this->paginate($query, $filter);
    }

    public function search(EloquentBuilder|QueryBuilder $query, QueryFilterDTO $filter, array $searchable = [])
    {
        $search = $filter->search;
        if ($search && $searchable) {
            // ILIKE is Postgres-only; SQLite LIKE is case-insensitive for ASCII.
            $operator = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($searchable, $search, $operator) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, $operator, "%{$search}%");
                }
            });
        }

        return $query;
    }

    public function sort(EloquentBuilder|QueryBuilder $query, QueryFilterDTO $filter, $defaultSort)
    {
        $query->orderBy($defaultSort, 'desc');
        if ($filter->sort_by) {
            $query->orderBy($filter->sort_by, $filter->sort_order ?? 'asc');
        }

        return $query;
    }

    public function paginate(EloquentBuilder|QueryBuilder $query, QueryFilterDTO $filter)
    {
        $limit = $filter->limit;
        $page = max($filter->page, 1);

        $total = $query->count();

        if ($limit) {
            $results = $query->forPage($page, $limit)->get();
            $meta = [
                'page' => $page,
                'limit' => (int) $limit,
                'total' => $total,
                'total_page' => (int) ceil($total / $limit),
            ];
        } else {
            $results = $query->get();
            $meta = [
                'total' => $total,
                'page' => 1,
                'limit' => $total,
                'total_page' => 1,
            ];
        }

        return [
            'data' => $results,
            'meta' => $meta,
        ];
    }

    /**
     * Same envelope as paginate(), but for an in-memory collection.
     * Used when sorting by derived values (client name, current stage,
     * computed progress) that cannot be ordered in SQL portably.
     */
    public function paginateCollection($collection, QueryFilterDTO $filter)
    {
        $collection = $collection instanceof \Illuminate\Support\Collection
            ? $collection
            : collect($collection);

        $total = $collection->count();
        $page = max($filter->page, 1);

        if ($filter->limit) {
            $results = $collection->forPage($page, $filter->limit)->values();
            $meta = [
                'page' => $page,
                'limit' => (int) $filter->limit,
                'total' => $total,
                'total_page' => (int) ceil($total / $filter->limit),
            ];
        } else {
            $results = $collection->values();
            $meta = [
                'total' => $total,
                'page' => 1,
                'limit' => $total,
                'total_page' => 1,
            ];
        }

        return [
            'data' => $results,
            'meta' => $meta,
        ];
    }
}

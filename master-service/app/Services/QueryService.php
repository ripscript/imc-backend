<?php

namespace App\Services;

use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
            $query->where(function ($q) use ($searchable, $search) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, 'ILIKE', "%{$search}%");
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
}

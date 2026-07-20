<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class QueryService
{
    public function applyFiltersAndPaginate(EloquentBuilder|QueryBuilder $query, Request $request, array $searchable = [], $defaultSort = 'updated_at')
    {
        $query = $this->search($query, $request, $searchable);
        $query = $this->sort($query, $request, $defaultSort);
        return $this->paginate($query, $request);
    }

    public function search(EloquentBuilder|QueryBuilder $query, Request $request, array $searchable = [])
    {
        $search = $request->input('search');
        if ($search && $searchable) {
            $query->where(function ($q) use ($searchable, $search) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, 'ILIKE', "%{$search}%");
                }
            });
        }

        return $query;
    }

    public function sort(EloquentBuilder|QueryBuilder $query, Request $request, $defaultSort)
    {
        $query->orderBy($defaultSort, 'desc');
        if ($sortBy = $request->input('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }

    public function paginate(EloquentBuilder|QueryBuilder $query, Request $request)
    {
        $limit = $request->input('limit');
        $page  = max((int) $request->input('page', 1), 1);

        $total = $query->count();

        if ($limit) {
            $results = $query->forPage($page, $limit)->get();
            $meta = [
                'page'       => $page,
                'limit'      => (int) $limit,
                'total'      => $total,
                'total_page' => (int) ceil($total / $limit),
            ];
        } else {
            $results = $query->get();
            $meta = [
                'total'      => $total,
                'page'       => 1,
                'limit'      => $total,
                'total_page' => 1,
            ];
        }

        return [
            'data' => $results,
            'meta' => $meta
        ];
    }
}

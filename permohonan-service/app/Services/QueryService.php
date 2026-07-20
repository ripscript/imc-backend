<?php

namespace App\Services;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;

class QueryService
{
    public function applyFiltersAndPaginate(EloquentBuilder|QueryBuilder $query, Request $request, array $searchable = [], ?Closure $customSearch = null, $defaultSort = 'updated_at')
    {
        $query = $this->search($query, $request, $searchable, $customSearch);
        $query = $this->sort($query, $request, $defaultSort);
        return $this->paginate($query, $request);
    }

    public function search(EloquentBuilder|QueryBuilder $query, Request $request, array $searchable = [], ?Closure $customSearch = null)
    {
        $search = $request->input('search');
        if ($search && $searchable) {
            $query->where(function ($q) use ($searchable, $search) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, 'ILIKE', "%{$search}%");
                }
            });
        }

        if ($customSearch instanceof Closure) {
            $customSearch($query, $search);
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

        $hasGroupBy = false;
        if ($query instanceof EloquentBuilder) {
            $hasGroupBy = !empty($query->getQuery()->groups);
        } else if ($query instanceof QueryBuilder) {
            $hasGroupBy = !empty($query->groups);
        }
        if ($hasGroupBy) {
            $sql = $query->toSql();
            $bindings = $query->getBindings();

            $subQuery = DB::table(DB::raw("($sql) as sub"))->mergeBindings($query);
            $total = $subQuery->count();
        } else {
            $total = $query->count();
        }

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

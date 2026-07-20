<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class GlobalSearchService
{
    public function search($keyword, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        $tsQuery = $this->buildTsQuery($keyword);

        // Base Query
        $baseQuery = DB::table('global_search_index')
            ->select(
                'source_type',
                'title',
                'url',
                'content_slug',
                DB::raw("
                    ts_rank(search_vector, {$tsQuery}) +
                    similarity(title, ?) AS rank
                ")
            )
            ->addBinding($keyword)
            ->where(function ($q) use ($tsQuery, $keyword) {
                $q->whereRaw("search_vector @@ {$tsQuery}")
                ->orWhereRaw("title % ?", [$keyword]);
            });

        // Total Count
        $total = DB::table(DB::raw("({$baseQuery->toSql()}) as sub"))
            ->mergeBindings($baseQuery)
            ->count();

        // Data
        $data = $baseQuery
            ->orderByDesc('rank')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'page' => (int) $page,
                'limit' => (int) $limit,
                'total' => $total,
                'total_page' => ceil($total / $limit)
            ]
        ];
    }

    private function buildTsQuery($keyword)
    {
        $terms = explode(' ', trim($keyword));

        $query = collect($terms)
            ->filter()
            ->map(fn ($t) => $t . ':*')
            ->implode(' & ');

        return "to_tsquery('simple', '{$query}')";
    }
}
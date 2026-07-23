<?php

namespace App\Services;

use App\Models\Content;
use App\Services\DTOs\QueryFilterDTO;

class ContentService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = Content::select('id', 'type', 'is_show_home', 'parent_type', 'parent_id');
        $query->whereHas('parent', function ($qq) use ($filter) {
            if ($filter->is_active !== null) {
                $qq->where('is_active', $filter->is_active)->whereNotNull('id');
            } else {
                $qq->whereNotNull('id');
            }
        });
        $query->with(['parent' => function ($qq) {
            $qq->select('id', 'title', 'slug', 'is_active');
        }]);
        if ($filter->is_show_home !== null) {
            $query->where('is_show_home', $filter->is_show_home);
        }
        if ($filter->type !== null) {
            $query->where('type', $filter->type);
        }
        $data = $query->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
                'page' => 1,
                'limit' => $data->count(),
                'total_page' => 1,
            ],
        ];
    }
}

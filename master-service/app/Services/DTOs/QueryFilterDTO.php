<?php

namespace App\Services\DTOs;

class QueryFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?string $sort_by = null,
        public ?string $sort_order = 'asc',
        public ?int $limit = null,
        public int $page = 1,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            search: $request->input('search'),
            sort_by: $request->input('sort_by'),
            sort_order: $request->input('sort_order', 'asc'),
            limit: $request->input('limit') ? (int) $request->input('limit') : null,
            page: max((int) $request->input('page', 1), 1),
        );
    }
}

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
        public ?bool $is_active = null,
        public bool $without_pagination = false,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            search: $request->input('search'),
            sort_by: $request->input('sort_by'),
            sort_order: $request->input('sort_order', 'asc'),
            limit: $request->input('limit') ? (int) $request->input('limit') : null,
            page: max((int) $request->input('page', 1), 1),
            is_active: $request->has('is_active') ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN) : null,
            without_pagination: $request->boolean('without_pagination'),
        );
    }
}

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
        public ?bool $is_show_home = null,
        public ?string $type = null,
        public ?bool $has_sub = null,
        public ?bool $has_child = null,
        public ?bool $without_pagination = false,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            search: $request->input('search'),
            sort_by: $request->input('sort_by'),
            sort_order: $request->input('sort_order', 'asc'),
            limit: $request->input('limit') ? (int) $request->input('limit') : null,
            page: max((int) $request->input('page', 1), 1),
            is_active: $request->filled('is_active') ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            is_show_home: $request->filled('is_show_home') ? filter_var($request->input('is_show_home'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            type: $request->input('type'),
            has_sub: $request->filled('has_sub') ? filter_var($request->input('has_sub'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            has_child: $request->filled('has_child') ? filter_var($request->input('has_child'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            without_pagination: $request->boolean('without_pagination'),
        );
    }
}

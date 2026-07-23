<?php

namespace Tests\Unit;

use App\Services\DTOs\QueryFilterDTO;
use App\Services\QueryService;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Mockery;
use Tests\TestCase;

class QueryServiceTest extends TestCase
{
    public function test_can_instantiate_service()
    {
        $service = app()->make(QueryService::class);
        $this->assertInstanceOf(QueryService::class, $service);
    }

    public function test_search_applies_search_filter_when_search_param_provided()
    {
        $service = new QueryService;
        $filter = new QueryFilterDTO(search: 'test');
        $builder = Mockery::mock(EloquentBuilder::class);

        $builder->shouldReceive('where')
            ->once()
            ->with(Mockery::on(function ($closure) {
                $subQuery = Mockery::mock(EloquentBuilder::class);
                $subQuery->shouldReceive('orWhere')
                    ->once()
                    ->with('name', 'ILIKE', '%test%');
                $closure($subQuery);

                return true;
            }))
            ->andReturnSelf();

        $result = $service->search($builder, $filter, ['name']);
        $this->assertSame($builder, $result);
    }

    public function test_search_returns_query_when_no_search_param()
    {
        $service = new QueryService;
        $filter = new QueryFilterDTO;
        $builder = Mockery::mock(EloquentBuilder::class);

        $builder->shouldNotReceive('where');

        $result = $service->search($builder, $filter, ['name']);
        $this->assertSame($builder, $result);
    }

    public function test_search_returns_query_when_searchable_empty()
    {
        $service = new QueryService;
        $filter = new QueryFilterDTO(search: 'test');
        $builder = Mockery::mock(EloquentBuilder::class);

        $builder->shouldNotReceive('where');

        $result = $service->search($builder, $filter, []);
        $this->assertSame($builder, $result);
    }

    public function test_sort_applies_default_sort()
    {
        $service = new QueryService;
        $filter = new QueryFilterDTO;
        $builder = Mockery::mock(EloquentBuilder::class);

        $builder->shouldReceive('orderBy')
            ->once()
            ->with('updated_at', 'desc')
            ->andReturnSelf();

        $result = $service->sort($builder, $filter, 'updated_at');
        $this->assertSame($builder, $result);
    }

    public function test_sort_applies_custom_sort()
    {
        $service = new QueryService;
        $filter = new QueryFilterDTO(sort_by: 'name', sort_order: 'asc');
        $builder = Mockery::mock(EloquentBuilder::class);

        $builder->shouldReceive('orderBy')
            ->once()
            ->with('updated_at', 'desc')
            ->andReturnSelf();

        $builder->shouldReceive('orderBy')
            ->once()
            ->with('name', 'asc')
            ->andReturnSelf();

        $result = $service->sort($builder, $filter, 'updated_at');
        $this->assertSame($builder, $result);
    }

    public function test_paginate_with_limit()
    {
        $service = new QueryService;
        $filter = new QueryFilterDTO(limit: 10, page: 2);
        $builder = Mockery::mock(EloquentBuilder::class);

        $builder->shouldReceive('count')->once()->andReturn(25);
        $builder->shouldReceive('forPage')->with(2, 10)->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn(collect(['item1', 'item2']));

        $result = $service->paginate($builder, $filter);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertEquals(2, $result['meta']['page']);
        $this->assertEquals(10, $result['meta']['limit']);
        $this->assertEquals(25, $result['meta']['total']);
        $this->assertEquals(3, $result['meta']['total_page']);
    }

    public function test_paginate_without_limit()
    {
        $service = new QueryService;
        $filter = new QueryFilterDTO;
        $builder = Mockery::mock(EloquentBuilder::class);

        $builder->shouldReceive('count')->once()->andReturn(5);
        $builder->shouldReceive('get')->once()->andReturn(collect(['a', 'b', 'c']));

        $result = $service->paginate($builder, $filter);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertEquals(1, $result['meta']['page']);
        $this->assertEquals(5, $result['meta']['limit']);
        $this->assertEquals(5, $result['meta']['total']);
        $this->assertEquals(1, $result['meta']['total_page']);
    }

    public function test_apply_filters_and_paginate_integration()
    {
        $service = new QueryService;
        $filter = new QueryFilterDTO(search: 'test', limit: 5, page: 1);
        $builder = Mockery::mock(EloquentBuilder::class);

        $builder->shouldReceive('where')
            ->once()
            ->with(Mockery::on(function ($closure) {
                $subQuery = Mockery::mock(EloquentBuilder::class);
                $subQuery->shouldReceive('orWhere')
                    ->once()
                    ->with('name', 'ILIKE', '%test%');
                $closure($subQuery);

                return true;
            }))
            ->andReturnSelf();

        $builder->shouldReceive('orderBy')
            ->once()
            ->with('updated_at', 'desc')
            ->andReturnSelf();

        $builder->shouldReceive('count')->once()->andReturn(3);
        $builder->shouldReceive('forPage')->with(1, 5)->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn(collect(['result']));

        $result = $service->applyFiltersAndPaginate($builder, $filter, ['name']);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

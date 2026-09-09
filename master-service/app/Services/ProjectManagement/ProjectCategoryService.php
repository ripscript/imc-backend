<?php

namespace App\Services\ProjectManagement;

use App\Models\PmProjectCategory;
use App\Services\DTOs\ProjectCategoryDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\QueryService;
use Illuminate\Support\Facades\DB;

class ProjectCategoryService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = PmProjectCategory::query();

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name', 'code']);
    }

    public function detail($id)
    {
        return PmProjectCategory::findOrFail($id);
    }

    public function create(ProjectCategoryDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $category = new PmProjectCategory;
            $category->name = $dto->name;
            $category->code = $dto->code;
            $category->status = $dto->status ?? 'active';
            $category->created_by = $dto->created_by;
            $category->updated_by = $dto->updated_by;
            $category->save();

            if (empty($category->code)) {
                $category->code = ProjectCodeGenerator::projectCategory($category->id);
                $category->save();
            }

            return $category;
        });
    }

    public function update($id, ProjectCategoryDTO $dto)
    {
        $category = PmProjectCategory::findOrFail($id);

        if ($dto->name !== null) {
            $category->name = $dto->name;
        }
        if ($dto->status !== null) {
            $category->status = $dto->status;
        }
        $category->updated_by = $dto->updated_by;
        $category->save();

        return $category;
    }

    public function destroy($id)
    {
        PmProjectCategory::findOrFail($id)->delete();

        return true;
    }
}

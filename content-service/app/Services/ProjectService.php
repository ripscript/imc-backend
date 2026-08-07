<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentDetailProject;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Services\DTOs\ProjectDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    protected SaveContentsService $saveContentService;

    protected GlobalSearchService $globalSearchService;

    protected ParentContentService $parentContentService;

    public function __construct(
        protected QueryService $queryService
    ) {
        $this->saveContentService = new SaveContentsService;
        $this->globalSearchService = new GlobalSearchService;
        $this->parentContentService = new ParentContentService;
    }

    public function sideMenu()
    {
        return $this->parentContentService->getParentByType('project');
    }

    public function index(QueryFilterDTO $filter, int $contentId)
    {
        $content = Content::findOrFail($contentId);
        $parentSlug = $content->parent->full_url ?? '';

        $query = DB::table('content_detail_projects')
            ->where('content_id', $contentId)
            ->select(
                'id',
                'title',
                'is_publicated',
                'thumbnail_path',
                'publish_date',
                'slug',
                'duration',
                'location',
                'start_date',
                'updated_at'
            )
            ->selectRaw('? as parent_url', [$parentSlug]);

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['title']);
    }

    public function detail($id)
    {
        $project = ContentDetailProject::findOrFail($id);
        $tagIds = ContentTag::where([
            'content_type' => ContentDetailProject::class,
            'content_id' => $project->id,
        ])->pluck('tag_id')->toArray();
        $project->tag_id = $tagIds;
        $project->applyAdditionalContent($project);

        return $project;
    }

    public function create(ProjectDTO $dto)
    {
        DB::beginTransaction();
        try {
            $layoutId = MsLayout::findByType('project_detail')->id;

            $data = new ContentDetailProject;
            $data->content_id = $dto->content_id;
            $data->title = $dto->title;
            $data->slug = $dto->slug;
            $data->description = $dto->description;
            $data->thumbnail_path = $dto->thumbnail_path;
            $data->publish_date = $dto->publish_date;
            $data->is_publicated = $dto->is_publicated;
            $data->layout_id = $layoutId;
            $data->duration = $dto->duration;
            $data->location = $dto->location;
            $data->start_date = $dto->start_date;
            $data->budget = $dto->budget;
            $data->organization_address = $dto->organization_address;
            $data->organization_name = $dto->organization_name;
            $data->created_by = $dto->created_by;
            $data->updated_by = $dto->updated_by;
            $data->save();

            $this->saveContentService->syncTags(ContentDetailProject::class, $data->id, $dto->tag_id ?? [], $dto->created_by);
            $this->saveContentService->storeAdditionalContent(ContentDetailProject::class, $data, $dto->toArray(), $dto->created_by);

            if ($data->is_publicated) {
                $this->globalSearchService->insertProject($data->id);
            }

            DB::commit();

            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, ProjectDTO $dto)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailProject::findOrFail($id);

            $data->title = $dto->title;
            $data->slug = $dto->slug;
            $data->description = $dto->description;
            $data->thumbnail_path = $dto->thumbnail_path;
            $data->publish_date = $dto->publish_date;
            $data->is_publicated = $dto->is_publicated;
            $data->layout_id = MsLayout::findByType('project_detail')->id;
            $data->duration = $dto->duration;
            $data->location = $dto->location;
            $data->start_date = $dto->start_date;
            $data->budget = $dto->budget;
            $data->organization_address = $dto->organization_address;
            $data->organization_name = $dto->organization_name;
            $data->updated_by = $dto->updated_by;
            $data->save();

            $this->saveContentService->syncTags(ContentDetailProject::class, $data->id, $dto->tag_id ?? [], $dto->updated_by);
            $this->saveContentService->storeAdditionalContent(ContentDetailProject::class, $data, $dto->toArray(), $dto->updated_by);

            if ($data->is_publicated) {
                $this->globalSearchService->insertProject($data->id);
            } else {
                $this->globalSearchService->delete(ContentDetailProject::class, $data->id);
            }

            DB::commit();

            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailProject::findOrFail($id);
            $data->delete();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

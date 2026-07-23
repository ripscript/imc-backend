<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentDetailRepository;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\RepositoryDTO;
use Illuminate\Support\Facades\DB;

class RepositoryService
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
        return $this->parentContentService->getParentByType('repository');
    }

    public function index(QueryFilterDTO $filter, int $contentId)
    {
        $content = Content::findOrFail($contentId);
        $parentSlug = $content->parent->full_url ?? '';

        $query = DB::table('content_detail_repositories')
            ->where('content_id', $contentId)
            ->select(
                'id',
                'title',
                'is_publicated',
                'thumbnail_path',
                'publish_date',
                'slug',
                'updated_at'
            )
            ->selectRaw('? as parent_url', [$parentSlug]);

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['title']);
    }

    public function detail($id)
    {
        $repository = ContentDetailRepository::findOrFail($id);
        $tagIds = ContentTag::where([
            'content_type' => ContentDetailRepository::class,
            'content_id' => $repository->id,
        ])->pluck('tag_id')->toArray();
        $repository->tag_id = $tagIds;

        return $repository;
    }

    public function create(RepositoryDTO $dto)
    {
        DB::beginTransaction();
        try {
            $layoutId = MsLayout::findByType('repository_detail')->id;

            $data = new ContentDetailRepository;
            $data->content_id = $dto->content_id;
            $data->title = $dto->title;
            $data->slug = $dto->slug;
            $data->file_path = $dto->file_path;
            $data->thumbnail_path = $dto->thumbnail_path;
            $data->description = $dto->description;
            $data->publish_date = $dto->publish_date;
            $data->is_publicated = $dto->is_publicated;
            $data->layout_id = $layoutId;
            $data->created_by = $dto->created_by;
            $data->updated_by = $dto->updated_by;
            $data->save();

            $this->saveContentService->syncTags(ContentDetailRepository::class, $data->id, $dto->tag_id ?? [], $dto->created_by);

            if ($data->is_publicated) {
                $this->globalSearchService->insertRepository($data->id);
            }

            DB::commit();

            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, RepositoryDTO $dto)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailRepository::findOrFail($id);

            $data->title = $dto->title;
            $data->slug = $dto->slug;
            $data->file_path = $dto->file_path;
            $data->thumbnail_path = $dto->thumbnail_path;
            $data->description = $dto->description;
            $data->publish_date = $dto->publish_date;
            $data->is_publicated = $dto->is_publicated;
            $data->layout_id = MsLayout::findByType('repository_detail')->id;
            $data->updated_by = $dto->updated_by;
            $data->save();

            $this->saveContentService->syncTags(ContentDetailRepository::class, $data->id, $dto->tag_id ?? [], $dto->updated_by);

            if ($data->is_publicated) {
                $this->globalSearchService->insertRepository($data->id);
            } else {
                $this->globalSearchService->delete(ContentDetailRepository::class, $data->id);
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
            $data = ContentDetailRepository::findOrFail($id);
            $data->delete();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

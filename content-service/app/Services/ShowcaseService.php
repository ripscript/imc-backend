<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentDetailShowcase;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\ShowcaseDTO;
use Illuminate\Support\Facades\DB;

class ShowcaseService
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
        return $this->parentContentService->getParentByType('showcase');
    }

    public function index(QueryFilterDTO $filter, int $contentId)
    {
        $content = Content::findOrFail($contentId);
        $parentSlug = $content->parent->full_url ?? '';

        $query = DB::table('content_detail_showcases')
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
        $showcase = ContentDetailShowcase::findOrFail($id);
        $tagIds = ContentTag::where([
            'content_type' => ContentDetailShowcase::class,
            'content_id' => $showcase->id,
        ])->pluck('tag_id')->toArray();
        $showcase->tag_id = $tagIds;
        $showcase->applyAdditionalContent($showcase);

        return $showcase;
    }

    public function create(ShowcaseDTO $dto)
    {
        DB::beginTransaction();
        try {
            $layoutId = MsLayout::findByType('showcase_detail')->id;

            $data = new ContentDetailShowcase;
            $data->content_id = $dto->content_id;
            $data->title = $dto->title;
            $data->slug = $dto->slug;
            $data->description = $dto->description;
            $data->kategori_content_id = $dto->kategori_content_id;
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

            $this->saveContentService->syncTags(ContentDetailShowcase::class, $data->id, $dto->tag_id ?? [], $dto->created_by);
            $this->saveContentService->storeAdditionalContent(ContentDetailShowcase::class, $data, $dto->toArray(), $dto->created_by);

            if ($data->is_publicated) {
                $this->globalSearchService->insertShowcase($data->id);
            }

            DB::commit();

            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, ShowcaseDTO $dto)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailShowcase::findOrFail($id);

            $data->title = $dto->title;
            $data->slug = $dto->slug;
            $data->description = $dto->description;
            $data->kategori_content_id = $dto->kategori_content_id;
            $data->thumbnail_path = $dto->thumbnail_path;
            $data->publish_date = $dto->publish_date;
            $data->is_publicated = $dto->is_publicated;
            $data->layout_id = MsLayout::findByType('showcase_detail')->id;
            $data->duration = $dto->duration;
            $data->location = $dto->location;
            $data->start_date = $dto->start_date;
            $data->budget = $dto->budget;
            $data->organization_address = $dto->organization_address;
            $data->organization_name = $dto->organization_name;
            $data->updated_by = $dto->updated_by;
            $data->save();

            $this->saveContentService->syncTags(ContentDetailShowcase::class, $data->id, $dto->tag_id ?? [], $dto->updated_by);
            $this->saveContentService->storeAdditionalContent(ContentDetailShowcase::class, $data, $dto->toArray(), $dto->updated_by);

            if ($data->is_publicated) {
                $this->globalSearchService->insertShowcase($data->id);
            } else {
                $this->globalSearchService->delete(ContentDetailShowcase::class, $data->id);
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
            $data = ContentDetailShowcase::findOrFail($id);
            $data->delete();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

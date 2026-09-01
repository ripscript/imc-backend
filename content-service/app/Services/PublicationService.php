<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentDetailPublication;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Services\DTOs\PublicationDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class PublicationService
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
        return $this->parentContentService->getParentByType('publication');
    }

    public function index(QueryFilterDTO $filter, int $contentId)
    {
        $content = Content::findOrFail($contentId);
        $parentSlug = $content->parent->full_url ?? '';

        $query = DB::table('content_detail_publications')
            ->where('content_id', $contentId)
            ->select(
                'id',
                'title',
                'description',
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
        $publication = ContentDetailPublication::findOrFail($id);
        $tagIds = ContentTag::where([
            'content_type' => ContentDetailPublication::class,
            'content_id' => $publication->id,
        ])->pluck('tag_id')->toArray();
        $publication->tag_id = $tagIds;
        $publication->applyAdditionalContent($publication);

        return $publication;
    }

    public function create(PublicationDTO $dto)
    {
        DB::beginTransaction();
        try {
            $layoutId = MsLayout::findByType('publication_detail')->id;

            $data = new ContentDetailPublication;
            $data->content_id = $dto->content_id;
            $data->title = $dto->title;
            $data->slug = $dto->slug;
            $data->headline = $dto->headline;
            $data->description = $dto->description ?? '';
            $data->publish_date = $dto->publish_date;
            $data->is_publicated = $dto->is_publicated;
            $data->is_slider = $dto->is_slider ?? false;
            $data->layout_id = $layoutId;
            $data->thumbnail_path = $dto->thumbnail_path;
            $data->kategori_content_id = $dto->kategori_content_id ?? null;
            $data->created_by = $dto->created_by;
            $data->updated_by = $dto->updated_by;
            $data->save();

            $this->saveContentService->syncTags(ContentDetailPublication::class, $data->id, $dto->tag_id ?? [], $dto->created_by);
            $this->saveContentService->storeAdditionalContent(ContentDetailPublication::class, $data, $dto->toArray(), $dto->created_by);

            if ($data->is_publicated) {
                $this->globalSearchService->insertPublication($data->id);
            }

            DB::commit();

            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, PublicationDTO $dto)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailPublication::findOrFail($id);

            $data->title = $dto->title;
            $data->slug = $dto->slug;
            $data->headline = $dto->headline;
            $data->description = $dto->description ?? '';
            $data->publish_date = $dto->publish_date;
            $data->is_publicated = $dto->is_publicated;
            $data->is_slider = $dto->is_slider ?? $data->is_slider;
            $data->thumbnail_path = $dto->thumbnail_path;
            $data->kategori_content_id = $dto->kategori_content_id ?? $data->kategori_content_id;
            $data->updated_by = $dto->updated_by;
            $data->save();

            $this->saveContentService->syncTags(ContentDetailPublication::class, $data->id, $dto->tag_id ?? [], $dto->updated_by);
            $this->saveContentService->storeAdditionalContent(ContentDetailPublication::class, $data, $dto->toArray(), $dto->updated_by);

            if ($data->is_publicated) {
                $this->globalSearchService->insertPublication($data->id);
            } else {
                $this->globalSearchService->delete(ContentDetailPublication::class, $data->id);
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
            $data = ContentDetailPublication::findOrFail($id);
            $data->delete();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

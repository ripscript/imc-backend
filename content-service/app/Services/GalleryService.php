<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentDetailGallery;
use App\Models\ContentDetailGalleryList;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Services\DTOs\GalleryDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class GalleryService
{
    protected SaveContentsService $saveContentService;

    protected GlobalSearchService $globalSearchService;

    protected ParentContentService $parentContentService;

    protected bool $uploadVideoEnabled;

    public function __construct(
        protected QueryService $queryService
    ) {
        $this->saveContentService = new SaveContentsService;
        $this->globalSearchService = new GlobalSearchService;
        $this->parentContentService = new ParentContentService;
        $this->uploadVideoEnabled = config('app.upload_video_enabled');
    }

    public function sideMenu()
    {
        return $this->parentContentService->getParentByType('gallery');
    }

    public function index(QueryFilterDTO $filter, int $contentId)
    {
        $content = Content::findOrFail($contentId);
        $parentSlug = $content->parent->full_url ?? '';

        $query = DB::table('content_detail_galleries')
            ->where('content_id', $contentId)
            ->select(
                'id',
                'title',
                'is_publicated',
                'publish_date',
                'slug',
                'updated_at'
            )
            ->selectRaw('? as parent_url', [$parentSlug]);

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['title']);
    }

    public function detail($id)
    {
        $gallery = ContentDetailGallery::with('list')->findOrFail($id);
        $tagIds = ContentTag::where([
            'content_type' => ContentDetailGallery::class,
            'content_id' => $gallery->id,
        ])->pluck('tag_id')->toArray();
        $gallery->tag_id = $tagIds;

        return $gallery;
    }

    public function create(GalleryDTO $dto)
    {
        DB::beginTransaction();
        try {
            $content = Content::find($dto->content_id);
            $galleryType = $content->sub_type ?? 'image';
            $layoutId = MsLayout::findByType('gallery_'.$galleryType.'_detail')->id;

            $data = new ContentDetailGallery;
            $data->content_id = $dto->content_id;
            $data->title = $dto->title;
            $data->slug = $dto->slug;
            $data->description = $dto->description ?? '';
            $data->publish_date = $dto->publish_date;
            $data->is_publicated = $dto->is_publicated;
            $data->layout_id = $layoutId;
            $data->created_by = $dto->created_by;
            $data->updated_by = $dto->updated_by;
            $data->save();

            if ($dto->list) {
                foreach ($dto->list as $key => $value) {
                    $dataDetail = new ContentDetailGalleryList;
                    $dataDetail->content_detail_gallery_id = $data->id;
                    $dataDetail->description = $value['description'] ?? '';
                    if ($galleryType == 'image') {
                        $dataDetail->image_path = $value['image_path'] ?? '';
                    } else {
                        $type = $this->uploadVideoEnabled ? ($value['video_type'] ?? 'link') : 'link';
                        $dataDetail->video_type = $type;
                        $dataDetail->video_url = $type == 'link' ? ($value['video_url'] ?? null) : null;
                        $dataDetail->video_path = $type == 'upload' ? ($value['video_path'] ?? null) : null;
                    }
                    $dataDetail->order = $key + 1;
                    $dataDetail->created_by = $dto->created_by;
                    $dataDetail->updated_by = $dto->updated_by;
                    $dataDetail->save();
                }
            }

            $this->saveContentService->syncTags(ContentDetailGallery::class, $data->id, $dto->tag_id ?? [], $dto->created_by);

            if ($data->is_publicated) {
                $this->globalSearchService->insertGallery($data->id);
            }

            DB::commit();

            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, GalleryDTO $dto)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailGallery::findOrFail($id);
            $content = $data->content;
            $galleryType = $content->sub_type ?? 'image';

            $data->title = $dto->title;
            $data->slug = $dto->slug;
            $data->description = $dto->description ?? '';
            $data->publish_date = $dto->publish_date;
            $data->is_publicated = $dto->is_publicated;
            $data->updated_by = $dto->updated_by;
            $data->save();

            if ($dto->list) {
                foreach ($dto->list as $key => $value) {
                    if (($value['id'] ?? 0) == 0) {
                        $dataDetail = new ContentDetailGalleryList;
                        $dataDetail->content_detail_gallery_id = $data->id;
                        $dataDetail->created_by = $dto->updated_by;
                    } else {
                        $dataDetail = ContentDetailGalleryList::findOrFail($value['id']);
                    }
                    $dataDetail->description = $value['description'] ?? '';
                    if ($galleryType == 'image') {
                        $dataDetail->image_path = $value['image_path'] ?? '';
                    } else {
                        $type = $this->uploadVideoEnabled ? ($value['video_type'] ?? 'link') : 'link';
                        $dataDetail->video_type = $type;
                        $dataDetail->video_url = $type == 'link' ? ($value['video_url'] ?? null) : null;
                        $dataDetail->video_path = $type == 'upload' ? ($value['video_path'] ?? null) : null;
                    }
                    $dataDetail->order = $key + 1;
                    $dataDetail->updated_by = $dto->updated_by;
                    $dataDetail->save();
                }
            }

            if ($dto->list && isset($dto->toArray()['deleted_content_detail_id'])) {
                ContentDetailGalleryList::whereIn('id', $dto->toArray()['deleted_content_detail_id'])->delete();
            }

            $this->saveContentService->syncTags(ContentDetailGallery::class, $data->id, $dto->tag_id ?? [], $dto->updated_by);

            if ($data->is_publicated) {
                $this->globalSearchService->insertGallery($data->id);
            } else {
                $this->globalSearchService->delete(ContentDetailGallery::class, $data->id);
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
            $data = ContentDetailGallery::findOrFail($id);
            $data->delete();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

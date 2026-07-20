<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Content;
use App\Models\ContentDetailGallery;
use App\Models\ContentDetailGalleryList;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Models\MsTag;
use App\Services\GlobalSearchService;
use App\Services\ParentContentService;
use App\Services\SaveContentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    protected $parentContentService;
    protected $saveContentService;
    protected $uploadVideoEnabled;

    public function __construct()
    {
        parent::__construct();
        $this->parentContentService = new ParentContentService();
        $this->saveContentService = new SaveContentsService();
        $this->uploadVideoEnabled = config('app.upload_video_enabled');
    }

    public function sideMenu()
    {
        try {
            $data = $this->parentContentService->getParentByType('gallery');
            return ApiResponse::success($data, 'Berhasil mengambil side menu untuk tipe galeri');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(Request $request, $contentId)
    {
        try {
            $content = Content::findOrFail($contentId);
            $parentSlug = $content->parent->full_url ?? "";
            
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
                ->selectRaw("
                    ? as parent_url
                ", [$parentSlug]);
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['title']);

            return ApiResponse::success($data, 'Berhasil mengambil data galeri');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'content_id' => 'required|exists:contents,id',
                'title' => 'required|string|max:255',
                'slug' => ['required', 'string', 'max:255', Rule::unique('content_detail_galleries')->where(function ($query) use ($request) {
                    $query->where('content_id', $request->content_id);
                })],
                'publish_date' => 'required',
                'is_publicated' => 'required|boolean',
                'tag_id' => 'nullable|array',
                'list' => 'required|array|min:1',
            ]);

            $user = auth()->user()->name;
            $content = Content::find($request->content_id);
            $galleryType = $content->sub_type ?? 'image';
            $layoutId = MsLayout::findByType('gallery_'. $galleryType .'_detail')->id;

            if ($galleryType == 'image') {
                $request->validate([
                    'list.*.image_path' => 'required'
                ]);
            } else {
                if ($this->uploadVideoEnabled) {
                    $request->validate([
                        'list.*.video_type' => 'required|in:upload,link',
                        'list.*.video_path' => 'required_if:list.*.video_type,upload',
                        'list.*.video_url'  => 'required_if:list.*.video_type,link',
                    ]);
                } else {
                    $request->validate([
                        'list.*.video_url'  => 'required',
                    ]);
                }
            }

            $data = new ContentDetailGallery();
            $data->content_id = $request->content_id;
            $data->title = $request->title;
            $data->slug = $request->slug;
            $data->description = $request->description ?? "";
            $data->publish_date = $request->publish_date;
            $data->is_publicated = $request->is_publicated;
            $data->layout_id = $layoutId;
            $data->created_by = $user;
            $data->updated_by = $user;
            $data->save();

            foreach ($request->list as $key => $value) {
                $dataDetail = new ContentDetailGalleryList();
                $dataDetail->content_detail_gallery_id = $data->id;
                $dataDetail->description = $value['description'];
                if ($galleryType == 'image') {
                    $dataDetail->image_path = $value['image_path'];
                } else {
                    $type = $this->uploadVideoEnabled ? $value['video_type'] : 'link';
                    $dataDetail->video_type = $type;
                    $dataDetail->video_url = $type == 'link' ? $value['video_url'] : null;
                    $dataDetail->video_path = $type == 'upload' ? $value['video_path'] : null;
                }
                $dataDetail->order = $key + 1;
                $dataDetail->created_by = $user;
                $dataDetail->updated_by = $user;
                $dataDetail->save();
            }

            $this->saveContentService->syncTags(ContentDetailGallery::class, $data->id, $request->tag_id, $user);

            if ($data->is_publicated) {
                $service = new GlobalSearchService();
                $service->insertGallery($data->id);
            }

            DB::commit();
            return ApiResponse::success($data, 'Berhasil menambahkan data galeri', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $gallery = ContentDetailGallery::with('list')->findOrFail($id);
            $tagIds = ContentTag::where([
                'content_type' => ContentDetailGallery::class, 
                'content_id' => $gallery->id
            ])->pluck('tag_id')->toArray();
            $gallery->tag_id = $tagIds;
            return ApiResponse::success($gallery, 'Berhasil mengambil data detail galeri');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailGallery::findOrFail($id);

            $request->validate([
                'title' => 'required|string|max:255',
                'slug' => ['required', 'string', 'max:255', Rule::unique('content_detail_galleries')->where(function ($query) use ($data) {
                    $query->where('content_id', $data->content_id);
                })->ignore($id)],
                'publish_date' => 'required',
                'is_publicated' => 'required|boolean',
                'tag_id' => 'nullable|array',
                'list' => 'required|array|min:1',
                'list.*.id' => 'required',
            ]);

            $user = auth()->user()->name;
            $content = $data->content;
            $galleryType = $content->sub_type ?? 'image';

            if ($galleryType == 'image') {
                $request->validate([
                    'list.*.image_path' => 'required'
                ]);
            } else {
                if ($this->uploadVideoEnabled) {
                    $request->validate([
                        'list.*.video_type' => 'required|in:upload,link',
                        'list.*.video_path' => 'required_if:list.*.video_type,upload',
                        'list.*.video_url'  => 'required_if:list.*.video_type,link',
                    ]);
                } else {
                    $request->validate([
                        'list.*.video_url'  => 'required',
                    ]);
                }
            }

            $data->title = $request->title;
            $data->slug = $request->slug;
            $data->description = $request->description ?? "";
            $data->publish_date = $request->publish_date;
            $data->is_publicated = $request->is_publicated;
            $data->updated_by = $user;
            $data->save();

            foreach ($request->list as $key => $value) {
                if ($value['id'] == 0) {
                    $dataDetail = new ContentDetailGalleryList();
                    $dataDetail->content_detail_gallery_id = $data->id;
                    $dataDetail->created_by = $user;
                } else {
                    $dataDetail = ContentDetailGalleryList::findOrFail($value['id']);
                }
                $dataDetail->description = $value['description'];
                if ($galleryType == 'image') {
                    $dataDetail->image_path = $value['image_path'];
                } else {
                    $type = $this->uploadVideoEnabled ? $value['video_type'] : 'link';
                    $dataDetail->video_type = $type;
                    $dataDetail->video_url = $type == 'link' ? $value['video_url'] : null;
                    $dataDetail->video_path = $type == 'upload' ? $value['video_path'] : null;
                }
                $dataDetail->order = $key + 1;
                $dataDetail->updated_by = $user;
                $dataDetail->save();
            }

            if ($request->filled('deleted_content_detail_id')) {
                ContentDetailGalleryList::whereIn('id', $request->deleted_content_detail_id)->delete();
            }

            $this->saveContentService->syncTags(ContentDetailGallery::class, $data->id, $request->tag_id, $user);

            $service = new GlobalSearchService();
            if ($data->is_publicated) {
                $service->insertGallery($data->id);
            } else {
                $service->delete(ContentDetailGallery::class, $data->id);
            }

            DB::commit();
            return ApiResponse::success($data, 'Berhasil memperbarui data galeri');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailGallery::findOrfail($id);
            $data->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus galeri');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

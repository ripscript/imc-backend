<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Content;
use App\Models\ContentDetailPublication;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Models\MsTag;
use App\Services\GlobalSearchService;
use App\Services\ParentContentService;
use App\Services\SaveContentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PublicationController extends Controller
{
    protected $parentContentService;
    protected $saveContentService;

    public function __construct()
    {
        parent::__construct();
        $this->parentContentService = new ParentContentService();
        $this->saveContentService = new SaveContentsService();
    }

    public function sideMenu()
    {
        try {
            $data = $this->parentContentService->getParentByType('publication');
            return ApiResponse::success($data, 'Berhasil mengambil side menu untuk tipe publikasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(Request $request, $contentId)
    {
        try {
            $content = Content::findOrFail($contentId);
            $parentSlug = $content->parent->full_url ?? "";

            $query = DB::table('content_detail_publications')
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
                ->selectRaw("
                    ? as parent_url
                ", [$parentSlug]);
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['title']);

            return ApiResponse::success($data, 'Berhasil mengambil data publikasi');
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
                'slug' => ['required', 'string', 'max:255', Rule::unique('content_detail_publications')->where(function ($query) use ($request) {
                    $query->where('content_id', $request->content_id);
                })],
                'headline' => 'required',
                'content' => 'required',
                'publish_date' => 'required',
                'is_publicated' => 'required|boolean',
                'kategori_content_id' => 'nullable|exists:ms_kategori_contents,id',
                'tag_id' => 'nullable|array'
            ]);

            $user = auth()->user()->name;
            $layoutId = MsLayout::findByType('publication_detail')->id;

            $data = new ContentDetailPublication();
            $data->content_id = $request->content_id;
            $data->title = $request->title;
            $data->slug = $request->slug;
            $data->headline = $request->headline;
            $data->content = $request->content ?? "";
            $data->publish_date = $request->publish_date;
            $data->is_publicated = $request->is_publicated;
            $data->is_slider = $request->is_slider ?? false;
            $data->layout_id = $layoutId;
            $data->thumbnail_path = $request->thumbnail_path;
            $data->kategori_content_id = $request->kategori_content_id ?? null;
            $data->created_by = $user;
            $data->updated_by = $user;
            $data->save();

            $this->saveContentService->syncTags(ContentDetailPublication::class, $data->id, $request->tag_id, $user);
            $this->saveContentService->storeAdditionalContent(ContentDetailPublication::class, $data, $request, $user);

            if ($data->is_publicated) {
                $service = new GlobalSearchService();
                $service->insertPublication($data->id);
            }

            DB::commit();
            return ApiResponse::success($data, 'Berhasil menambahkan data publikasi', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $publication = ContentDetailPublication::findOrFail($id);
            $tagIds = ContentTag::where([
                'content_type' => ContentDetailPublication::class, 
                'content_id' => $publication->id
            ])->pluck('tag_id')->toArray();
            $publication->tag_id = $tagIds;
            $publication->applyAdditionalContent($publication);
            return ApiResponse::success($publication, 'Berhasil mengambil data detail publikasi');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailPublication::findOrFail($id);

            $request->validate([
                'title' => 'required|string|max:255',
                'slug' => ['required', 'string', 'max:255', Rule::unique('content_detail_publications')->where(function ($query) use ($data) {
                    $query->where('content_id', $data->content_id);
                })->ignore($id)],
                'headline' => 'required',
                'content' => 'required',
                'publish_date' => 'required',
                'is_publicated' => 'required|boolean',
                'kategori_content_id' => 'nullable|exists:ms_kategori_contents,id',
                'tag_id' => 'nullable|array'
            ]);

            $user = auth()->user()->name;

            $data->title = $request->title;
            $data->slug = $request->slug;
            $data->headline = $request->headline;
            $data->content = $request->content ?? "";
            $data->publish_date = $request->publish_date;
            $data->is_publicated = $request->is_publicated;
            $data->is_slider = $request->is_slider ?? $data->is_slider;
            $data->thumbnail_path = $request->thumbnail_path;
            $data->kategori_content_id = $request->kategori_content_id ?? $data->kategori_content_id;
            $data->updated_by = $user;
            $data->save();
 
            $this->saveContentService->syncTags(ContentDetailPublication::class, $data->id, $request->tag_id, $user);
            $this->saveContentService->storeAdditionalContent(ContentDetailPublication::class, $data, $request, $user);

            $service = new GlobalSearchService();
            if ($data->is_publicated) {
                $service->insertPublication($data->id);
            } else {
                $service->delete(ContentDetailPublication::class, $data->id);
            }
            
            DB::commit();
            return ApiResponse::success($data, 'Berhasil memperbarui data publikasi');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailPublication::findOrfail($id);
            $data->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus publikasi');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

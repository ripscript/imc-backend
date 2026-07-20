<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Content;
use App\Models\ContentDetailRepository;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Models\MsTag;
use App\Services\GlobalSearchService;
use App\Services\ParentContentService;
use App\Services\SaveContentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RepositoryController extends Controller
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
            $data = $this->parentContentService->getParentByType('repository');
            return ApiResponse::success($data, 'Berhasil mengambil side menu untuk tipe repositori');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(Request $request, $contentId)
    {
        try {
            $content = Content::findOrFail($contentId);
            $parentSlug = $content->parent->full_url ?? "";

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
                ->selectRaw("
                    ? as parent_url
                ", [$parentSlug]);
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['title']);

            return ApiResponse::success($data, 'Berhasil mengambil data repositori');
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
                'slug' => ['required', 'string', 'max:255', Rule::unique('content_detail_repositories')->where(function ($query) use ($request) {
                    $query->where('content_id', $request->content_id);
                })],
                'file_path' => 'required',
                'thumbnail_path' => 'required',
                'publish_date' => 'required',
                'is_publicated' => 'required|boolean',
                'tag_id' => 'nullable|array'
            ]);

            $user = auth()->user()->name;
            $layoutId = MsLayout::findByType('repository_detail')->id;

            $data = new ContentDetailRepository();
            $data->content_id = $request->content_id;
            $data->title = $request->title;
            $data->slug = $request->slug;
            $data->file_path = $request->file_path;
            $data->thumbnail_path = $request->thumbnail_path;
            $data->description = $request->description;
            $data->publish_date = $request->publish_date;
            $data->is_publicated = $request->is_publicated;
            $data->layout_id = $layoutId;
            $data->created_by = $user;
            $data->updated_by = $user;
            $data->save();

            $this->saveContentService->syncTags(ContentDetailRepository::class, $data->id, $request->tag_id, $user);

            if ($data->is_publicated) {
                $service = new GlobalSearchService();
                $service->insertRepository($data->id);
            }

            DB::commit();
            return ApiResponse::success($data, 'Berhasil menambahkan data repository', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $repository = ContentDetailRepository::findOrFail($id);
            $tagIds = ContentTag::where([
                'content_type' => ContentDetailRepository::class, 
                'content_id' => $repository->id
            ])->pluck('tag_id')->toArray();
            $repository->tag_id = $tagIds;
            return ApiResponse::success($repository, 'Berhasil mengambil data detail repository');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailRepository::findOrFail($id);

            $request->validate([
                'title' => 'required|string|max:255',
                'slug' => ['required', 'string', 'max:255', Rule::unique('content_detail_repositories')->where(function ($query) use ($data) {
                    $query->where('content_id', $data->content_id);
                })->ignore($id)],
                'file_path' => 'required',
                'thumbnail_path' => 'required',
                'publish_date' => 'required',
                'is_publicated' => 'required|boolean',
                'tag_id' => 'nullable|array'
            ]);

            $user = auth()->user()->name;
            $layoutId = MsLayout::findByType('repository_detail')->id;

            $data->title = $request->title;
            $data->slug = $request->slug;
            $data->file_path = $request->file_path;
            $data->thumbnail_path = $request->thumbnail_path;
            $data->description = $request->description;
            $data->publish_date = $request->publish_date;
            $data->is_publicated = $request->is_publicated;
            $data->layout_id = $layoutId;
            $data->updated_by = $user;
            $data->save();

            $this->saveContentService->syncTags(ContentDetailRepository::class, $data->id, $request->tag_id, $user);

            $service = new GlobalSearchService();
            if ($data->is_publicated) {
                $service->insertRepository($data->id);
            } else {
                $service->delete(ContentDetailRepository::class, $data->id);
            }

            DB::commit();
            return ApiResponse::success($data, 'Berhasil memperbarui data repository');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailRepository::findOrfail($id);
            $data->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus repository');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

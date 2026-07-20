<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ChildSubMenu;
use App\Models\Content;
use App\Services\GlobalSearchService;
use App\Services\SaveContentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChildSubMenuController extends Controller
{
    protected $saveContentService;

    public function __construct()
    {
        parent::__construct();
        $this->saveContentService = new SaveContentsService();
    }

    public function index(Request $request)
    {
        try {
            $query = DB::table('child_sub_menus')
                ->join('sub_menus', 'child_sub_menus.sub_menu_id', '=', 'sub_menus.id')
                ->join('menus', 'sub_menus.menu_id', '=', 'menus.id')
                ->leftJoin('contents', function ($join) {
                    $join->on('child_sub_menus.id', '=', 'contents.parent_id')
                        ->where('contents.parent_type', '=', ChildSubMenu::class);
                })
                ->select(
                    'child_sub_menus.id',
                    'menus.title as menu_title',
                    'sub_menus.title as sub_menu_title',
                    'child_sub_menus.title',
                    'child_sub_menus.is_active',
                    'child_sub_menus.slug',
                    'contents.type',
                    'child_sub_menus.updated_at'
                )
                ->selectRaw("
                    '/' || menus.slug || '/' || sub_menus.slug || '/' || child_sub_menus.slug as url
                ");

            if ($request->filled('without_pagination') && $request->without_pagination == true) {
                $data = $query->get();
            } else {
                $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['child_sub_menus.title', 'contents.type']);
            }

            return ApiResponse::success($data, 'Berhasil mengambil data child sub menu');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $childSubMenu = ChildSubMenu::findOrFail($id);
            // $childSubMenu->menu_id = $childSubMenu->subMenu->menu_id;

            $content = Content::where(['parent_type' => ChildSubMenu::class, 'parent_id' => $childSubMenu->id])->first();
            $childSubMenu->applyContent($content);
            $childSubMenu->applyAdditionalContent($content);

            return ApiResponse::success($childSubMenu, 'Berhasil mengambil data detail child sub menu');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'sub_menu_id' => 'required|exists:sub_menus,id',
                'slug' => ['required', 'string', 'max:200', Rule::unique('child_sub_menus', 'slug')->where(function ($query) use ($request) {
                    $query->where('sub_menu_id', $request->sub_menu_id);
                })],
                'title' => 'required|string|max:200',
                'is_active' => 'required',
                'order' => 'required',
                'type' => 'required',
                'layout_id' => 'required',
                'is_accessible' => 'boolean',
                'is_show_home' => 'boolean',
                'is_show_tag' => 'boolean',
            ]);

            $user = auth()->user()->name;

            $childSubMenu = new ChildSubMenu();
            $childSubMenu->sub_menu_id = $request->sub_menu_id;
            $childSubMenu->title = $request->title;
            $childSubMenu->slug = $request->slug;
            $childSubMenu->is_active = $request->is_active;
            $childSubMenu->order = $request->order;
            $childSubMenu->created_by = $user;
            $childSubMenu->updated_by = $user;
            $childSubMenu->save();

            $content = new Content();
            $content->parent_type = ChildSubMenu::class;
            $content->parent_id = $childSubMenu->id;
            $content->type = $request->type;
            $content->layout_id = $request->layout_id;
            $content->created_by = $user;
            $content->updated_by = $user;
            $content->is_accessible = $childSubMenu->is_active ? true : ($request->is_accessible ?? false);
            if (in_array($request->type, ['publication', 'repository', 'gallery'])) {
                $content->is_show_home = $request->is_show_home;
                $content->is_show_tag = $request->is_show_tag;

                if ($request->type == 'gallery') {
                    $content->sub_type = $request->sub_type ?? 'image';
                }
            }
            $content->save();

            $this->saveContentService->store($content, $request, $user);
            $this->saveContentService->storeAdditionalContent(Content::class, $content, $request, $user);

            if ($content->is_accessible && !$content->is_section) {
                $service = new GlobalSearchService();
                $service->insertContent($content->id);
            }

            DB::commit();
            return ApiResponse::success($childSubMenu, 'Berhasil menambahkan data child sub menu', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $childSubMenu = ChildSubMenu::findOrFail($id);

            $request->validate([
                'sub_menu_id' => 'required|exists:sub_menus,id',
                'slug' => ['required', 'string', 'max:200', Rule::unique('child_sub_menus', 'slug')->where(function ($query) use ($childSubMenu) {
                    $query->where('sub_menu_id', $childSubMenu->sub_menu_id);
                })->ignore($id)],
                'title' => 'required|string|max:200',
                'is_active' => 'required',
                'order' => 'required',
                'layout_id' => 'required',
                'is_accessible' => 'boolean',
                'is_show_home' => 'boolean',
                'is_show_tag' => 'boolean',
            ]);

            $user = auth()->user()->name;

            $childSubMenu->sub_menu_id = $request->sub_menu_id;
            $childSubMenu->title = $request->title;
            $childSubMenu->slug = $request->slug;
            $childSubMenu->is_active = $request->is_active;
            $childSubMenu->order = $request->order;
            $childSubMenu->updated_by = $user;
            $childSubMenu->save();

            $content = Content::findOrFail($childSubMenu->content?->id);
            $content->parent_type = ChildSubMenu::class;
            $content->parent_id = $childSubMenu->id;
            $content->layout_id = $request->layout_id;
            $content->updated_by = $user;
            $content->is_accessible = $childSubMenu->is_active ? true : ($request->is_accessible ?? false);
            if (in_array($request->type, ['publication', 'repository', 'gallery'])) {
                $content->is_show_home = $request->is_show_home;
                $content->is_show_tag = $request->is_show_tag;
            }
            $content->save();

            $this->saveContentService->store($content, $request, $user);
            $this->saveContentService->storeAdditionalContent(Content::class, $content, $request, $user);

            $service = new GlobalSearchService();
            if ($content->is_accessible && !$content->is_section) {
                $service->insertContent($content->id);
            } else {
                $service->delete(ChildSubMenu::class, $content->id);
            }

            DB::commit();
            return ApiResponse::success($childSubMenu, 'Berhasil memperbarui data child sub menu', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = ChildSubMenu::findOrfail($id);
            $data->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus child sub menu');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Content;
use App\Models\SubMenu;
use App\Services\GlobalSearchService;
use App\Services\SaveContentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubMenuController extends Controller
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
            $query = DB::table('sub_menus')
                ->join('menus', 'sub_menus.menu_id', '=', 'menus.id')
                ->leftJoin('contents', function ($join) {
                    $join->on('sub_menus.id', '=', 'contents.parent_id')
                        ->where('contents.parent_type', '=', SubMenu::class);
                })
                ->select(
                    'sub_menus.id',
                    'menus.title as menu_title',
                    'sub_menus.title',
                    'sub_menus.is_active',
                    'sub_menus.slug',
                    'contents.type',
                    'sub_menus.has_child',
                    'sub_menus.updated_at'
                )
                ->selectRaw("
                    CASE 
                        WHEN sub_menus.has_child = false 
                        THEN '/' || menus.slug || '/' || sub_menus.slug
                        ELSE NULL
                    END as url
                ");
            if ($request->filled('has_child')) {
                $query->where('sub_menus.has_child', $request->has_child);
            }
            
            if ($request->filled('without_pagination') && $request->without_pagination == true) {
                $data = $query->get();
            } else {
                $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['sub_menus.title', 'contents.type']);
            }

            return ApiResponse::success($data, 'Berhasil mengambil data sub menu');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $subMenu = SubMenu::findOrFail($id);

            if (!$subMenu->has_child) {
                $content = Content::where(['parent_type' => SubMenu::class, 'parent_id' => $subMenu->id])->first();
                $subMenu->applyContent($content);
                $subMenu->applyAdditionalContent($content);
            }

            return ApiResponse::success($subMenu, 'Berhasil mengambil data detail sub menu');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'menu_id' => 'required|exists:menus,id',
                'slug' => ['required', 'string', 'max:200', Rule::unique('sub_menus', 'slug')->where(function ($query) use ($request) {
                    $query->where('menu_id', $request->menu_id);
                })],
                'title' => 'required|string|max:200',
                'has_child' => 'required',
                'is_active' => 'required',
                'order' => 'required',
                'type' => 'required_if:has_child,false',
                'layout_id' => 'required_if:has_child,false',
                'is_accessible' => 'boolean',
                'is_show_home' => 'boolean',
                'is_show_tag' => 'boolean',
            ]);

            $user = auth()->user()->name;

            $subMenu = new SubMenu();
            $subMenu->menu_id = $request->menu_id;
            $subMenu->title = $request->title;
            $subMenu->slug = $request->slug;
            $subMenu->is_active = $request->is_active;
            $subMenu->order = $request->order;
            $subMenu->created_by = $user;
            $subMenu->updated_by = $user;
            $subMenu->has_child = $request->has_child;
            $subMenu->is_show_side_menu = $request->is_show_side_menu ?? false;
            $subMenu->save();

            if ($subMenu->has_child == false) {
                $content = new Content();
                $content->parent_type = SubMenu::class;
                $content->parent_id = $subMenu->id;
                $content->type = $request->type;
                $content->layout_id = $request->layout_id;
                $content->created_by = $user;
                $content->updated_by = $user;
                $content->is_accessible = $subMenu->is_active ? true : ($request->is_accessible ?? false);
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
            }

            DB::commit();
            return ApiResponse::success($subMenu, 'Berhasil menambahkan data sub menu', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $subMenu = SubMenu::findOrFail($id);
            $request->merge([
                'has_child' => $subMenu->has_child,
            ]);
            
            $request->validate([
                'menu_id' => 'required|exists:menus,id',
                'slug' => ['required', 'string', 'max:200', Rule::unique('sub_menus', 'slug')->where(function ($query) use ($subMenu) {
                    $query->where('menu_id', $subMenu->menu_id);
                })->ignore($id)],
                'title' => 'required|string|max:200',
                'is_active' => 'required',
                'order' => 'required',
                'layout_id' => 'required_if:has_child,false',
                'is_accessible' => 'boolean',
                'is_show_home' => 'boolean',
                'is_show_tag' => 'boolean',
            ]);

            $user = auth()->user()->name;

            $subMenu->menu_id = $request->menu_id;
            $subMenu->title = $request->title;
            $subMenu->slug = $request->slug;
            $subMenu->is_active = $request->is_active;
            $subMenu->order = $request->order;
            $subMenu->updated_by = $user;
            $subMenu->is_show_side_menu = $request->is_show_side_menu ?? $subMenu->is_show_side_menu;
            $subMenu->save();

            if ($subMenu->has_child == false) {
                $content = Content::findOrFail($subMenu->content?->id);
                $content->parent_type = SubMenu::class;
                $content->parent_id = $subMenu->id;
                $content->layout_id = $request->layout_id;
                $content->updated_by = $user;
                $content->is_accessible = $subMenu->is_active ? true : ($request->is_accessible ?? false);
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
                    $service->delete(SubMenu::class, $content->id);
                }
            }

            DB::commit();
            return ApiResponse::success($subMenu, 'Berhasil memperbarui data sub menu', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = SubMenu::findOrfail($id);
            if ($data->childSubMenu()->exists()) {
                DB::rollBack();
                return ApiResponse::error('Sub menu masih memiliki child sub menu', '', 409);
            }
            $data->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus sub menu');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

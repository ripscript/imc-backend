<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Content;
use App\Models\Menu;
use App\Models\SiteSetting;
use App\Services\GlobalSearchService;
use App\Services\QueryService;
use App\Services\SaveContentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    protected $saveContentService;

    public function __construct()
    {
        parent::__construct();
        $this->saveContentService = new SaveContentsService();
    }

    public static function maxMenusShowFront()
    {
        return SiteSetting::first()->value('menu_max_show_front') ?? 5;
    }

    public function index(Request $request)
    {
        try {
            $query = DB::table('menus')
                ->leftJoin('contents', function ($join) {
                    $join->on('menus.id', '=', 'contents.parent_id')
                        ->where('contents.parent_type', '=', Menu::class)
                        ->where('is_section', '!=', true);
                })
                ->select(
                    'menus.id',
                    'menus.title',
                    'menus.is_active',
                    'menus.slug',
                    'contents.type',
                    'menus.has_sub',
                    'menus.updated_at'
                )
                ->selectRaw("
                    CASE 
                        WHEN menus.has_sub = false 
                        THEN '/' || menus.slug
                        ELSE NULL
                    END as url
                ");
            if ($request->filled('has_sub')) {
                $query->where('menus.has_sub', $request->has_sub);
            }

            if ($request->filled('has_child')) {
                $query->whereExists(function ($subQuery) use ($request) {
                    $subQuery->select(DB::raw(1))
                        ->from('sub_menus as sm')
                        ->whereRaw('sm.menu_id = menus.id AND sm.has_child = ' . $request->has_child);
                });
            }
            
            if ($request->filled('without_pagination') && $request->without_pagination == true) {
                $data = $query->get();
            } else {
                $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['menus.title', 'contents.type']);
            }

            return ApiResponse::success($data, 'Berhasil mengambil data menu');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $menu = Menu::findOrFail($id);

            if (!$menu->has_sub) {
                $content = Content::where(['parent_type' => Menu::class, 'parent_id' => $menu->id])->first();
                $menu->applyContent($content);
                $menu->applyAdditionalContent($content);
            }

            return ApiResponse::success($menu, 'Berhasil mengambil data detail menu');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'slug' => ['required', 'string', 'max:200', Rule::unique('menus', 'slug')],
                'title' => 'required|string|max:200',
                'has_sub' => 'required',
                'is_active' => 'required',
                'is_show_front' => 'required|boolean',
                'order' => 'required',
                'type' => 'required_if:has_sub,false',
                'layout_id' => 'required_if:has_sub,false',
                'is_accessible' => 'boolean',
                'is_show_home' => 'boolean',
                'is_show_tag' => 'boolean',
            ]);

            $user = auth()->user()->name;

            $menu = new Menu();
            $menu->title = $request->title;
            $menu->slug = $request->slug;
            $menu->is_active = $request->is_active;
            $menu->order = $request->order;
            $menu->created_by = $user;
            $menu->updated_by = $user;
            $menu->has_sub = $request->has_sub;
            $menu->is_show_front = $request->is_show_front;
            $menu->is_show_side_menu = $request->is_show_side_menu ?? false;

            if ($menu->is_show_front == true && $menu->countMenusWithShowFront() >= MenuController::maxMenusShowFront()) {
                DB::rollBack();
                $message = "Anda telah mencapai batas maksimum untuk menampilkan menu prioritas di halaman depan (Maksimal ". MenuController::maxMenusShowFront() .")";
                return ApiResponse::error($message, null, 409);
            }

            $menu->save();

            if ($menu->has_sub == false) {
                $content = new Content();
                $content->parent_type = Menu::class;
                $content->parent_id = $menu->id;
                $content->type = $request->type;
                $content->layout_id = $request->layout_id;
                $content->created_by = $user;
                $content->updated_by = $user;
                $content->is_accessible = $menu->is_active ? true : ($request->is_accessible ?? false);
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
            return ApiResponse::success($menu, 'Berhasil menambahkan data menu', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $menu = Menu::findOrFail($id);
            $request->merge([
                'has_sub' => $menu->has_sub,
            ]);

            $request->validate([
                'slug' => ['required', 'string', 'max:200', Rule::unique('menus', 'slug')->ignore($id)],
                'title' => 'required|string|max:200',
                'is_active' => 'required',
                'is_show_front' => 'required|boolean',
                'order' => 'required',
                'layout_id' => 'required_if:has_sub,false',
                'is_accessible' => 'boolean',
                'is_show_home' => 'boolean',
                'is_show_tag' => 'boolean',
            ]);

            $user = auth()->user()->name;

            $menu->title = $request->title;
            $menu->slug = $request->slug;
            $menu->is_active = $request->is_active;
            $menu->order = $request->order;
            $menu->updated_by = $user;
            $menu->is_show_front = $request->is_show_front;
            $menu->is_show_side_menu = $request->is_show_side_menu ?? $menu->is_show_side_menu;

            if ($menu->is_show_front == true && $menu->countMenusWithShowFront() >= MenuController::maxMenusShowFront()) {
                DB::rollBack();
                $message = "Anda telah mencapai batas maksimum untuk menampilkan menu prioritas di halaman depan (Maksimal ". MenuController::maxMenusShowFront() .")";
                return ApiResponse::error($message, null, 409);
            }

            $menu->save();

            if ($menu->has_sub == false) {
                $content = Content::findOrFail($menu->content?->id);
                $content->parent_type = Menu::class;
                $content->parent_id = $menu->id;
                $content->layout_id = $request->layout_id;
                $content->updated_by = $user;
                $content->is_accessible = $menu->is_active ? true : ($request->is_accessible ?? false);
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
                    $service->delete(Menu::class, $content->id);
                }
            }

            DB::commit();
            return ApiResponse::success($menu, 'Berhasil memperbarui data menu', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Menu::findOrfail($id);
            if ($data->is_dedicated) {
                DB::rollBack();
                return ApiResponse::error('Menu tidak boleh dihapus', '', 403);
            }
            if ($data->subMenu()->exists()) {
                DB::rollBack();
                return ApiResponse::error('Menu masih memiliki sub menu', '', 409);
            }
            $data->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus menu');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

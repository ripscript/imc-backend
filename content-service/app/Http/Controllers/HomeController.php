<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\LogActivity;
use App\Models\Content;
use App\Models\Menu;
use App\Models\MsLayout;
use App\Services\SaveContentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    protected $saveContentService;
    CONST SLUG_BERANDA = 'beranda';
    CONST LOG_NAME = 'section beranda';

    public function __construct()
    {
        parent::__construct();
        $this->saveContentService = new SaveContentsService();
    }

    public function index(Request $request)
    {
        try {
            $query = DB::table('contents')
                ->join('menus', function ($join) {
                    $join->on('menus.id', '=', 'contents.parent_id')
                        ->where('contents.parent_type', '=', Menu::class);
                })
                ->where('menus.slug', '=', self::SLUG_BERANDA)
                ->select(
                    'contents.id',
                    'contents.order',
                    'contents.type',
                    'contents.section_title',
                    'contents.is_section'
                );

            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['contents.section_title', 'contents.type'], 'contents.updated_at');

            return ApiResponse::success($data, 'Berhasil mengambil data section');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $content = Content::findOrFail($id);
            $content->content = $content->getDetailBasedOnType();
    
            return ApiResponse::success($content, 'Berhasil mengambil data detail section');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'section_title' => 'required|string|max:200',
                'is_section' => 'required',
                'order' => 'required',
                'type' => 'required'
            ]);

            $beranda = Menu::where('slug', self::SLUG_BERANDA)->first();
            if (!$beranda) {
                DB::rollBack();
                ApiResponse::success('Menu beranda tidak ditemukan', 404);
            }
            $user = auth()->user()->name;
            $layoutId = MsLayout::findByType('landing_page')->id;

            $content = new Content();
            $content->parent_type = Menu::class;
            $content->parent_id = $beranda->id;
            $content->section_title = $request->section_title;
            $content->is_section = $request->is_section;
            $content->order = $request->order;
            $content->type = $request->type;
            $content->layout_id = $layoutId;
            $content->created_by = $user;
            $content->updated_by = $user;
            $content->save();

            $this->saveContentService->store($content, $request, $user);

            LogActivity::create(self::LOG_NAME, $content, 'create');

            DB::commit();
            return ApiResponse::success($content, 'Berhasil menambahkan data section', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $content = Content::findOrFail($id);

            $request->validate([
                'section_title' => 'required|string|max:200',
                'is_section' => 'required',
                'order' => 'required',
            ]);

            $user = auth()->user()->name;

            $content->is_section = $request->is_section;
            $content->section_title = $request->section_title;
            $content->order = $request->order;
            $content->updated_by = $user;
            $content->save();

            $this->saveContentService->store($content, $request, $user);

            LogActivity::create(self::LOG_NAME, $content, 'update');

            DB::commit();
            return ApiResponse::success($content, 'Berhasil memperbarui data section');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Content::findOrfail($id);
            $data->delete();

            LogActivity::create(self::LOG_NAME, $data, 'delete');

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus section');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

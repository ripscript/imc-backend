<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ContentDetailAgenda;
use App\Services\ParentContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    protected $parentContentService;

    public function __construct()
    {
        parent::__construct();
        $this->parentContentService = new ParentContentService();
    }

    public function sideMenu()
    {
        try {
            $data = $this->parentContentService->getParentByType('agenda');
            return ApiResponse::success($data, 'Berhasil mengambil side menu untuk tipe agenda');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(Request $request, $contentId)
    {
        try {
            $query = DB::table('content_detail_agendas')
                ->where('content_id', $contentId);
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['event_name']);

            return ApiResponse::success($data, 'Berhasil mengambil data agenda');
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
                'event_name' => 'required|string',
                'is_range' => 'required|boolean',
                'is_active' => 'required|boolean',
                'datetime_start' => 'required',
                'datetime_end' => 'required_if:is_range,true'
            ]);

            $user = auth()->user()->name;

            $data = new ContentDetailAgenda();
            $data->content_id = $request->content_id;
            $data->event_name = $request->event_name;
            $data->is_active = $request->is_active;
            $data->is_range = $request->is_range;
            $data->datetime_start = $request->datetime_start;
            $data->datetime_end = $request->is_range ? $request->datetime_end : null;
            $data->created_by = $user;
            $data->updated_by = $user;
            $data->save();

            DB::commit();
            return ApiResponse::success($data, 'Berhasil menambahkan data agenda', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $agenda = ContentDetailAgenda::findOrFail($id);
            return ApiResponse::success($agenda, 'Berhasil mengambil data detail agenda');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailAgenda::findOrFail($id);

            $request->validate([
                'event_name' => 'required|string',
                'is_range' => 'required|boolean',
                'is_active' => 'required|boolean',
                'datetime_start' => 'required',
                'datetime_end' => 'required_if:is_range,true'
            ]);

            $user = auth()->user()->name;

            $data->event_name = $request->event_name;
            $data->is_active = $request->is_active;
            $data->is_range = $request->is_range;
            $data->datetime_start = $request->datetime_start;
            $data->datetime_end = $request->is_range ? $request->datetime_end : null;
            $data->updated_by = $user;
            $data->save();

            DB::commit();
            return ApiResponse::success($data, 'Berhasil memperbarui data agenda');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailAgenda::findOrfail($id);
            $data->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus agenda');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

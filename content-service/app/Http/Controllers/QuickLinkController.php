<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\QuickLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuickLinkController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('quick_links')
                ->select('id','title', 'url', 'order', 'is_active');

            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['title', 'url']);

            return ApiResponse::success($data, 'Berhasil mengambil data link cepat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $link = QuickLink::findOrFail($id);
            return ApiResponse::success($link, 'Berhasil mengambil data link cepat');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'is_active' => 'required|boolean',
                'url' => 'nullable|string',
            ]);

            $user = auth()->user()->name;

            $link = new QuickLink();
            $link->title = $request->title;
            $link->url = $request->url;
            $link->order = $request->order ?? 0;
            $link->is_active = $request->is_active;
            $link->created_by = $user;
            $link->updated_by = $user;
            $link->save();

            DB::commit();
            return ApiResponse::success($link, 'Berhasil menambahkan data link cepat', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $link = QuickLink::findOrFail($id);
            
            $request->validate([
                'title' => 'required|string|max:255',
                'is_active' => 'required|boolean',
                'url' => 'nullable|string',
            ]);

            $user = auth()->user()->name;

            $link->title = $request->title;
            $link->url = $request->url;
            if ($request->filled('order')) {
                $link->order = $request->order;
            }
            $link->is_active = $request->is_active;
            $link->updated_by = $user;
            $link->save();

            DB::commit();
            return ApiResponse::success($link, 'Berhasil memperbarui data link cepat');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            QuickLink::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data link cepat');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

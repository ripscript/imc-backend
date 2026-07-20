<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MsTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_tags')->select('id','title', 'is_active');
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            if ($request->filled('without_pagination') && $request->without_pagination == true) {
                $data = $query->get();
            } else {
                $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['title']);
            }

            return ApiResponse::success($data, 'Berhasil mengambil data tag');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $tag = MsTag::findOrFail($id);
            return ApiResponse::success($tag, 'Berhasil mengambil data detail tag');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'title' => ['required', 'string', 'max:100', Rule::unique('ms_tags', 'title')],
                'is_active' => 'boolean'
            ]);

            $user = auth('admin')->user()->name;

            $tag = new MsTag();
            $tag->title = $request->title;
            $tag->is_active = $request->is_active;
            $tag->created_by = $user;
            $tag->updated_by = $user;
            $tag->save();

            DB::commit();
            return ApiResponse::success($tag, 'Berhasil menambahkan data tag', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $tag = MsTag::findOrFail($id);
            
            $request->validate([
                'title' => ['required', 'string', 'max:100', Rule::unique('ms_tags', 'title')->ignore($id)],
                'is_active' => 'boolean'
            ]);

            $user = auth('admin')->user()->name;

            $tag->title = $request->title;
            $tag->is_active = $request->is_active;
            $tag->updated_by = $user;
            $tag->save();

            DB::commit();
            return ApiResponse::success($tag, 'Berhasil memperbarui data tag');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsTag::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data tag');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}


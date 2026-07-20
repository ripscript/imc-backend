<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MsKategoriContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KategoriContentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_kategori_contents')->select('id','title', 'is_active');
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            if ($request->filled('without_pagination') && $request->without_pagination == true) {
                $data = $query->get();
            } else {
                $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['title']);
            }

            return ApiResponse::success($data, 'Berhasil mengambil data kategori konten');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $kategori = MsKategoriContent::findOrFail($id);
            return ApiResponse::success($kategori, 'Berhasil mengambil data detail kategori konten');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'title' => ['required', 'string', 'max:100', Rule::unique('ms_kategori_contents', 'title')],
                'is_active' => 'boolean'
            ]);

            $user = auth('admin')->user()->name;

            $kategori = new MsKategoriContent();
            $kategori->title = $request->title;
            $kategori->is_active = $request->is_active;
            $kategori->created_by = $user;
            $kategori->updated_by = $user;
            $kategori->save();

            DB::commit();
            return ApiResponse::success($kategori, 'Berhasil menambahkan data kategori konten', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $kategori = MsKategoriContent::findOrFail($id);
            
            $request->validate([
                'title' => ['required', 'string', 'max:100', Rule::unique('ms_kategori_contents', 'title')->ignore($id)],
                'is_active' => 'boolean'
            ]);

            $user = auth('admin')->user()->name;

            $kategori->title = $request->title;
            $kategori->is_active = $request->is_active;
            $kategori->updated_by = $user;
            $kategori->save();

            DB::commit();
            return ApiResponse::success($kategori, 'Berhasil memperbarui data kategori konten');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsKategoriContent::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data kategori konten');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

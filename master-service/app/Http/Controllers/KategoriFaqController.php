<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MsKategoriFaq;
use App\Rules\UniqueSoftDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KategoriFaqController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_kategori_faq')
                ->whereNull('deleted_at')
                ->select('id','name');

            if ($request->filled('without_pagination') && $request->without_pagination == true) {
                $data = $query->get();
            } else {
                $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['name']);
            }

            return ApiResponse::success($data, 'Berhasil mengambil data kategori FAQ');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $kategoriFaq = MsKategoriFaq::findOrFail($id);
            return ApiResponse::success($kategoriFaq, 'Berhasil mengambil data detail kategori FAQ');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_kategori_faq')],
            ]);

            $user = auth('admin')->user()->name;

            $kategoriFaq = new MsKategoriFaq();
            $kategoriFaq->name = $request->name;
            $kategoriFaq->created_by = $user;
            $kategoriFaq->updated_by = $user;
            $kategoriFaq->save();

            DB::commit();
            return ApiResponse::success($kategoriFaq, 'Berhasil menambahkan data kategori FAQ', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $kategoriFaq = MsKategoriFaq::findOrFail($id);
            
            $request->validate([
                'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_kategori_faq', 'name', $id)],
            ]);

            $user = auth('admin')->user()->name;

            $kategoriFaq->name = $request->name;
            $kategoriFaq->updated_by = $user;
            $kategoriFaq->save();

            DB::commit();
            return ApiResponse::success($kategoriFaq, 'Berhasil memperbarui data kategori FAQ');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsKategoriFaq::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data kategori FAQ');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

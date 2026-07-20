<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MsFaq;
use App\Models\MsKategoriFaq;
use App\Rules\UniqueSoftDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_faq')
                ->join('ms_kategori_faq', 'ms_faq.kategori_faq_id', '=', 'ms_kategori_faq.id')
                ->whereNull('ms_faq.deleted_at')
                ->whereNull('ms_kategori_faq.deleted_at')
                ->select('ms_faq.id', 'title', 'description', 'ms_kategori_faq.name as kategori_faq', 'ms_faq.is_active', 'ms_faq.updated_at');
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['title', 'description', 'ms_kategori_faq.name']);

            return ApiResponse::success($data, 'Berhasil mengambil data FAQ');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $faq = MsFaq::findOrFail($id);
            return ApiResponse::success($faq, 'Berhasil mengambil data detail FAQ');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'title' => ['required', 'string', 'max:255', new UniqueSoftDelete('ms_faq', 'title')],
                'description' => 'required|string',
                'is_active' => 'required|boolean',
                'kategori_faq_id' => 'required'
            ]);

            $user = auth('admin')->user()->name;

            $exists = MsKategoriFaq::find($request->kategori_faq_id);
            if (!$exists) {
                DB::rollBack();
                return ApiResponse::error('Kategori FAQ tidak valid', '', 422);
            }

            $faq = new MsFaq();
            $faq->title = $request->title;
            $faq->description = $request->description;
            $faq->is_active = $request->is_active;
            $faq->kategori_faq_id = $request->kategori_faq_id;
            $faq->created_by = $user;
            $faq->updated_by = $user;
            $faq->save();

            DB::commit();
            return ApiResponse::success($faq, 'Berhasil menambahkan data FAQ', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $faq = MsFaq::findOrFail($id);
            
            $request->validate([
                'title' => ['required', 'string', 'max:255', new UniqueSoftDelete('ms_faq', 'title', $id)],
                'description' => 'required|string',
                'is_active' => 'required|boolean',
                'kategori_faq_id' => 'required'
            ]);

            $user = auth('admin')->user()->name;

            $exists = MsKategoriFaq::find($request->kategori_faq_id);
            if (!$exists) {
                DB::rollBack();
                return ApiResponse::error('Kategori FAQ tidak valid', '', 422);
            }

            $faq->title = $request->title;
            $faq->description = $request->description;
            $faq->is_active = $request->is_active;
            $faq->kategori_faq_id = $request->kategori_faq_id;
            $faq->updated_by = $user;
            $faq->save();

            DB::commit();
            return ApiResponse::success($faq, 'Berhasil memperbarui data FAQ');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsFaq::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data FAQ');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

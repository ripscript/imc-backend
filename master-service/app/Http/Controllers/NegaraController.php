<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MsNegara;
use App\Rules\UniqueSoftDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NegaraController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_negara')
                ->whereNull('deleted_at')
                ->select('id','name');

            if ($request->filled('without_pagination') && $request->without_pagination == true) {
                $data = $query->get();
            } else {
                $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['name']);
            }

            return ApiResponse::success($data, 'Berhasil mengambil data negara');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $negara = MsNegara::findOrFail($id);
            return ApiResponse::success($negara, 'Berhasil mengambil data detail negara');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_negara')],
            ]);

            $user = auth('admin')->user()->name;

            $negara = new MsNegara();
            $negara->name = $request->name;
            $negara->created_by = $user;
            $negara->updated_by = $user;
            $negara->save();

            DB::commit();
            return ApiResponse::success($negara, 'Berhasil menambahkan data negara', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $negara = MsNegara::findOrFail($id);
            
            $request->validate([
                'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_negara', 'name', $id)],
            ]);

            $user = auth('admin')->user()->name;

            $negara->name = $request->name;
            $negara->updated_by = $user;
            $negara->save();

            DB::commit();
            return ApiResponse::success($negara, 'Berhasil memperbarui data negara');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsNegara::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data negara');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

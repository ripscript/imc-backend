<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MsAlasanKeberatan;
use App\Rules\UniqueSoftDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlasanKeberatanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_alasan_keberatan')
                ->whereNull('deleted_at')
                ->select('id','name', 'is_active');
            
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            if ($request->filled('without_pagination') && $request->without_pagination == true) {
                $data = $query->get();
            } else {
                $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['name']);
            }

            return ApiResponse::success($data, 'Berhasil mengambil data alasan keberatan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $alasan = MsAlasanKeberatan::findOrFail($id);
            return ApiResponse::success($alasan, 'Berhasil mengambil data alasan keberatan');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255', new UniqueSoftDelete('ms_alasan_keberatan', 'name')],
                'is_active' => 'required|boolean'
            ]);

            $user = auth('admin')->user()->name;

            $alasan = new MsAlasanKeberatan();
            $alasan->name = $request->name;
            $alasan->is_active = $request->is_active;
            $alasan->created_by = $user;
            $alasan->updated_by = $user;
            $alasan->save();

            DB::commit();
            return ApiResponse::success($alasan, 'Berhasil menambahkan data alasan keberatan', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $alasan = MsAlasanKeberatan::findOrFail($id);
            
            $request->validate([
                'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_alasan_keberatan', 'name', $id)],
                'is_active' => 'required|boolean'
            ]);

            $user = auth('admin')->user()->name;

            $alasan->name = $request->name;
            $alasan->is_active = $request->is_active;
            $alasan->updated_by = $user;
            $alasan->save();

            DB::commit();
            return ApiResponse::success($alasan, 'Berhasil memperbarui data alasan keberatan');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsAlasanKeberatan::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data alasan keberatan');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

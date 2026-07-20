<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MsSatuanKerja;
use App\Models\MsSubSatuanKerja;
use App\Rules\UniqueSoftDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SatuanKerjaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_satuan_kerja')
                ->whereNull('deleted_at')
                ->select('id','name');

            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['name']);
            
            return ApiResponse::success($data, 'Berhasil mengambil data satuan kerja');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $satuanKerja = MsSatuanKerja::with('subSatuanKerja:id,satuan_kerja_id,name')->findOrFail($id);
            $satuanKerja->sub = $satuanKerja->subSatuanKerja->pluck('name')->toArray();
            unset($satuanKerja->subSatuanKerja);
            return ApiResponse::success($satuanKerja, 'Berhasil mengambil data detail satuan kerja');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255', new UniqueSoftDelete('ms_satuan_kerja')],
                'sub' => 'nullable|array'
            ]);

            $user = auth('admin')->user()->name;

            $satuanKerja = new MsSatuanKerja();
            $satuanKerja->name = $request->name;
            $satuanKerja->created_by = $user;
            $satuanKerja->updated_by = $user;
            $satuanKerja->save();

            if (count($request->sub) > 0) {
                foreach ($request->sub as $subSatuanKerja) {
                    MsSubSatuanKerja::firstOrCreate([
                        'name' => $subSatuanKerja,
                        'satuan_kerja_id' => $satuanKerja->id
                    ],[
                        'created_by' => $user,
                        'updated_by' => $user
                    ]);
                }
            }

            DB::commit();
            return ApiResponse::success($satuanKerja, 'Berhasil menambahkan data satuan kerja', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $satuanKerja = MsSatuanKerja::findOrFail($id);
            
            $request->validate([
                'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_satuan_kerja', 'name', $id)],
                'sub' => 'nullable|array'
            ]);

            $user = auth('admin')->user()->name;

            $satuanKerja->name = $request->name;
            $satuanKerja->updated_by = $user;
            $satuanKerja->save();

            if (count($request->sub) > 0) {
                $subIds = [];
                foreach ($request->sub as $subSatuanKerja) {
                    $sub = MsSubSatuanKerja::firstOrCreate([
                        'name' => $subSatuanKerja,
                        'satuan_kerja_id' => $satuanKerja->id
                    ],[
                        'created_by' => $user,
                        'updated_by' => $user
                    ]);
                    $subIds[] = $sub->id;
                }
                if ($subIds) {
                    MsSubSatuanKerja::where('satuan_kerja_id', $satuanKerja->id)->whereNotIn('id', $subIds)->delete();
                }
            } else {
                MsSubSatuanKerja::where('satuan_kerja_id', $satuanKerja->id)->delete();
            }

            DB::commit();
            return ApiResponse::success($satuanKerja, 'Berhasil memperbarui data satuan kerja');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsSatuanKerja::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data satuan kerja');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getSub($id)
    {
        try {
            $data = DB::table('ms_sub_satuan_kerja')
                ->where('satuan_kerja_id', $id)
                ->whereNull('deleted_at')
                ->select('id','name')
                ->get();

            return ApiResponse::success($data, 'Berhasil mengambil data sub satuan kerja');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

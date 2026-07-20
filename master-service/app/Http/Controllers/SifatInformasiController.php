<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MsSifatInformasi;
use App\Rules\UniqueSoftDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SifatInformasiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_sifat_informasi')
                ->whereNull('deleted_at')
                ->select('id','name', 'icon_path');

            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['name']);

            return ApiResponse::success($data, 'Berhasil mengambil data sifat informasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $sifatInformasi = MsSifatInformasi::findOrFail($id);
            return ApiResponse::success($sifatInformasi, 'Berhasil mengambil data detail sifat informasi');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_sifat_informasi')],
                'icon_path' => 'nullable|string',
                'description' => 'nullable|string',
                'link' => 'nullable|string',
            ]);

            $user = auth('admin')->user()->name;

            $sifatInformasi = new MsSifatInformasi();
            $sifatInformasi->name = $request->name;
            $sifatInformasi->icon_path = $request->icon_path;
            $sifatInformasi->description = $request->description;
            $sifatInformasi->link = $request->link;
            $sifatInformasi->created_by = $user;
            $sifatInformasi->updated_by = $user;
            $sifatInformasi->save();

            DB::commit();
            return ApiResponse::success($sifatInformasi, 'Berhasil menambahkan data sifat informasi', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $sifatInformasi = MsSifatInformasi::findOrFail($id);
            
            $request->validate([
                'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_sifat_informasi', 'name', $id)],
                'icon_path' => 'nullable|string',
                'description' => 'nullable|string',
                'link' => 'nullable|string',
            ]);

            $user = auth('admin')->user()->name;

            $sifatInformasi->name = $request->name;
            $sifatInformasi->icon_path = $request->icon_path;
            $sifatInformasi->description = $request->description;
            $sifatInformasi->link = $request->link;
            $sifatInformasi->updated_by = $user;
            $sifatInformasi->save();

            DB::commit();
            return ApiResponse::success($sifatInformasi, 'Berhasil memperbarui data sifat informasi');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsSifatInformasi::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data sifat informasi');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}


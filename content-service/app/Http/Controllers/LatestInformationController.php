<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\LatestInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LatestInformationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('latest_informations')
                ->select('id','title', 'url', 'is_active', 'publish_date');

            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['title']);

            return ApiResponse::success($data, 'Berhasil mengambil data informasi terkini');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $informasi = LatestInformation::findOrFail($id);
            return ApiResponse::success($informasi, 'Berhasil mengambil data informasi terkini');
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
                'publish_date' => 'required'
            ]);

            $user = auth()->user()->name;

            $informasi = new LatestInformation();
            $informasi->title = $request->title;
            $informasi->url = $request->url;
            $informasi->publish_date = $request->publish_date;
            $informasi->is_active = $request->is_active;
            $informasi->created_by = $user;
            $informasi->updated_by = $user;
            $informasi->save();

            DB::commit();
            return ApiResponse::success($informasi, 'Berhasil menambahkan data informasi terkini', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $informasi = LatestInformation::findOrFail($id);
            
            $request->validate([
                'title' => 'required|string|max:255',
                'is_active' => 'required|boolean',
                'url' => 'nullable|string',
                'publish_date' => 'required'
            ]);

            $user = auth()->user()->name;

            $informasi->title = $request->title;
            $informasi->url = $request->url;
            $informasi->publish_date = $request->publish_date;
            $informasi->is_active = $request->is_active;
            $informasi->updated_by = $user;
            $informasi->save();

            DB::commit();
            return ApiResponse::success($informasi, 'Berhasil memperbarui data informasi terkini');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            LatestInformation::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data informasi terkini');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermohonanController extends Controller
{
    public function aksesData(Request $request)
    {
        try {
            $data = DB::table('ms_permohonan_akses_data')->get();
            return ApiResponse::success($data, 'Berhasil mengambil data master akses data permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function formatData(Request $request)
    {
        try {
            $data = DB::table('ms_permohonan_format_data')->get();
            return ApiResponse::success($data, 'Berhasil mengambil data master format data permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function salinanInformasi(Request $request)
    {
        try {
            $data = DB::table('ms_permohonan_salinan_informasi')->get();
            return ApiResponse::success($data, 'Berhasil mengambil data master salinan informasi permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

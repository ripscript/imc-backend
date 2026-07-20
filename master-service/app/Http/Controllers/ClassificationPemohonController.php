<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassificationPemohonController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = DB::table('ms_classification_pemohon')->get();
            return ApiResponse::success($data, 'Berhasil mengambil data klasifikasi pemohon');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = DB::table('ms_roles')->get();

            return ApiResponse::success($data, 'Berhasil mengambil data role');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

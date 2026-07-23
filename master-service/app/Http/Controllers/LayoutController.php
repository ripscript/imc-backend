<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LayoutController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_layout')
                ->select('id', 'title');

            if ($request->filled('is_custom')) {
                $query->where('is_custom', '=', $request->is_custom);
            }

            if ($request->filled('type')) {
                $query->where('type', '=', $request->type);
            }

            $data = $query->get();

            return ApiResponse::success($data, 'Berhasil mengambil data layout');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

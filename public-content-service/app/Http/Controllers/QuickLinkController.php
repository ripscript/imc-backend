<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuickLinkController extends Controller
{
    public function index()
    {
        try {
            $data = DB::table('quick_links')
                ->select(
                    'title',
                    'url',
                    'order'
                )
                ->where('is_active', true)
                ->orderBy('order')
                ->get();

            return ApiResponse::success($data, 'Berhasil mengambil data link cepat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

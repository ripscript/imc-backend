<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        try {
            $data = DB::table('site_settings')
                ->select(
                    'address',
                    'phone',
                    'email',
                    'facebook_url',
                    'instagram_url',
                    'youtube_url',
                    'twitter_url'
                )
                ->first();

            return ApiResponse::success($data, 'Berhasil mengambil data pengaturan website');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

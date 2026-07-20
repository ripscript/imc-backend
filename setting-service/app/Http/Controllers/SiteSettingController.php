<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\LogActivity;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteSettingController extends Controller
{
    public function index()
    {
        try {
            $data = SiteSetting::first();
            return ApiResponse::success($data, 'Berhasil mengambil data pengaturan website');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = SiteSetting::first();
    
            if (!$data) {
                return ApiResponse::error('Template pengaturan website tidak ditemukan', '', 404);
            }

            $request->validate([
                'address' => 'required|string',
                'phone' => 'required|string',
                'wa_number' => 'required|string',
                'email' => 'required|string|email',
                'facebook_url' => 'nullable|string',
                'youtube_url' => 'nullable|string',
                'instagram_url' => 'nullable|string',
                'twitter_url' => 'nullable|string',
                'max_file_size' => 'nullable|integer'
            ]);

            $user = auth()->user()->name;

            $data->address = $request->address;
            $data->phone = $request->phone;
            $data->wa_number = $request->wa_number;
            $data->email = $request->email;
            $data->facebook_url = $request->facebook_url;
            $data->youtube_url = $request->youtube_url;
            $data->instagram_url = $request->instagram_url;
            $data->twitter_url = $request->twitter_url;
            if ($request->filled('permohonan_max_destination_institution')) {
                $data->permohonan_max_destination_institution = $request->permohonan_max_destination_institution;
            }
            if ($request->filled('menu_max_show_front')) {
                $data->menu_max_show_front = $request->menu_max_show_front;
            }
            if ($request->filled('max_file_size')) {
                $data->max_file_size = $request->max_file_size;
            }
            $data->updated_by = $user;
            $data->save();

            DB::commit();

            LogActivity::createCustom('pengaturan website', 'Memperbarui pengaturan website', $data);
            
            return ApiResponse::success($data, 'Berhasil memperbarui data pengaturan website');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

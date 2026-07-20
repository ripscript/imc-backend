<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\LogActivity;
use App\Models\ChatSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatSettingController extends Controller
{
    public function index()
    {
        try {
            $data = ChatSetting::first();
            return ApiResponse::success($data, 'Berhasil mengambil data pengaturan chat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = ChatSetting::first();
    
            if (!$data) {
                return ApiResponse::error('Template pengaturan chat tidak ditemukan', '', 404);
            }

            $request->validate([
                'opening' => 'required|string',
                'closing' => 'required|string',
                'confirmation' => 'required|string',
                'another_question' => 'required|string',
                'to_whatsapp' => 'required|string',
            ]);

            $user = auth()->user()->name;

            $data->opening = $request->opening;
            $data->closing = $request->closing;
            $data->confirmation = $request->confirmation;
            $data->another_question = $request->another_question;
            $data->to_whatsapp = $request->to_whatsapp;
            $data->updated_by = $user;
            $data->save();

            DB::commit();

            LogActivity::createCustom('pengaturan chat', 'Memperbarui pengaturan chat', $data);
            
            return ApiResponse::success($data, 'Berhasil memperbarui data pengaturan chat');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

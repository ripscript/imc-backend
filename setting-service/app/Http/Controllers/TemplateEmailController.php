<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\TemplateEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplateEmailController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = DB::table('template_emails')->orderBy('id')->get();
            return ApiResponse::success($data, 'Berhasil mengambil data template email');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $template = TemplateEmail::findOrFail($id);
            return ApiResponse::success($template, 'Berhasil mengambil data detail template email');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $template = TemplateEmail::findOrFail($id);
            
            $request->validate([
                'content' => 'required|string',
                'subject' => 'nullable|string'
            ]);

            $user = auth()->user()->name;

            $template->content = $request->content;
            $template->subject = $request->subject;
            $template->updated_by = $user;
            $template->save();

            DB::commit();
            return ApiResponse::success($template, 'Berhasil memperbarui data template email');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateAll(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'data' => 'required|array',
                'data.*.id' => 'required|integer',
                'data.*.content' => 'required|string',
                'data.*.subject' => 'nullable|string',
            ]);

            $user = auth()->user()->name;

            foreach ($request->data as $value) {
                $template = TemplateEmail::findOrFail($value['id']);
                $template->content = $value['content'];
                $template->subject = $value['subject'] ?? '';
                $template->updated_by = $user;
                $template->save();
            }

            DB::commit();
            return ApiResponse::success('', 'Berhasil memperbarui data template email');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

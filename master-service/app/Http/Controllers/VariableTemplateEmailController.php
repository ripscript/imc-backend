<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VariableTemplateEmailController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_variable_template_email');
            if ($request->filled('template_email')) {
                $query->where('template_email', $request->template_email);
            }
            $data = $query->get();

            return ApiResponse::success($data, 'Berhasil mengambil data variabel template email');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

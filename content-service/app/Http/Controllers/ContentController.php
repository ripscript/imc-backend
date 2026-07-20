<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Content::select('id', 'type', 'is_show_home', 'parent_type', 'parent_id');
            $query->whereHas('parent', function ($qq) use ($request) {
                if ($request->filled('is_active')) {
                    $qq->where('is_active', $request->is_active)->whereNotNull('id');
                } else {
                    $qq->whereNotNull('id');
                }
            });
            $query->with(['parent' => function ($qq) {
                $qq->select('id', 'title', 'slug', 'is_active');
            }]);
            if ($request->filled('is_show_home')) {
                $query->where('is_show_home', $request->is_show_home);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            $data = $query->get();

            return ApiResponse::success($data, 'Berhasil mengambil list content');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

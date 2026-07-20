<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'slug'        => 'required|string|max:255',
                'latitude'    => 'nullable|numeric|between:-90,90',
                'longitude'   => 'nullable|numeric|between:-180,180',
                'country'     => 'nullable|string|max:255',
                'city'        => 'nullable|string|max:255',
                'user_agent'  => 'nullable|string',
                'device_type' => 'nullable|string|max:100|in:desktop,tablet,mobile,unknown',
            ]);

            Visitor::create([
                'ip_address' => $request->ip(),
                'slug' => $request->slug,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'country' => $request->country,
                'city' => $request->city,
                'user_agent' => $request->user_agent,
                'device_type' => $request->device_type
            ]);

            DB::commit();
            return ApiResponse::success('', 'Berhasil menyimpan data pengunjung');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}

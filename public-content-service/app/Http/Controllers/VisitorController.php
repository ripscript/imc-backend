<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateVisitorRequest;
use App\Services\VisitorService;

class VisitorController extends Controller
{
    public function __construct(
        protected VisitorService $visitorService
    ) {}

    public function create(CreateVisitorRequest $request)
    {
        $this->visitorService->create([
            'ip_address' => $request->ip(),
            'slug' => $request->slug,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'country' => $request->country,
            'city' => $request->city,
            'user_agent' => $request->user_agent,
            'device_type' => $request->device_type,
        ]);

        return ApiResponse::success('', 'Berhasil menyimpan data pengunjung');
    }
}

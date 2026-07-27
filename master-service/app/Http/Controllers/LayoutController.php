<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\LayoutResource;
use App\Services\LayoutService;
use Illuminate\Http\Request;

class LayoutController extends Controller
{
    public function __construct(
        protected LayoutService $layoutService
    ) {}

    public function index(Request $request)
    {
        $data = $this->layoutService->index($request);

        return ApiResponse::success(LayoutResource::collection($data), 'Data layout berhasil diambil');
    }
}

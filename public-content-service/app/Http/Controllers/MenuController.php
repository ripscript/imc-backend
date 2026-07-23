<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\MenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->menuService->index($request);

            return ApiResponse::success($data, 'Berhasil mengambil list menu');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

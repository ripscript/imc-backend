<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\MenuIndexRequest;
use App\Services\DTOs\MenuFilterDTO;
use App\Services\MenuService;

class MenuController extends Controller
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    public function index(MenuIndexRequest $request)
    {
        $dto = new MenuFilterDTO($request->validated());
        $data = $this->menuService->index($dto);

        return ApiResponse::success($data, 'Berhasil mengambil list menu');
    }
}

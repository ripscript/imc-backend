<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\QuickLinkService;

class QuickLinkController extends Controller
{
    public function __construct(
        protected QuickLinkService $quickLinkService
    ) {}

    public function index()
    {
        try {
            $data = $this->quickLinkService->index();

            return ApiResponse::success($data, 'Berhasil mengambil data link cepat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

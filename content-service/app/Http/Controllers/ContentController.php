<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ContentService;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        protected ContentService $contentService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->contentService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil list content');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

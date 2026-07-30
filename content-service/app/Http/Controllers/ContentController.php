<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ContentService;
use App\Services\DTOs\QueryFilterDTO;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        protected ContentService $contentService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Content::class);
        $data = $this->contentService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil list content');
    }
}

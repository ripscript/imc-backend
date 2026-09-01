<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateQuickLinkRequest;
use App\Http\Requests\UpdateQuickLinkRequest;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\QuickLinkDTO;
use App\Services\QuickLinkService;
use App\Models\QuickLink;
use Illuminate\Http\Request;

class QuickLinkController extends Controller
{
    public function __construct(protected QuickLinkService $quickLinkService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', QuickLink::class);
        return ApiResponse::success($this->quickLinkService->index(QueryFilterDTO::fromRequest($request)), 'Berhasil mengambil data link cepat');
    }

    public function detail($id)
    {
        $result = $this->quickLinkService->detail($id);
        $this->authorize('view', $result);
        return ApiResponse::success($result, 'Berhasil mengambil data link cepat');
    }

    public function create(CreateQuickLinkRequest $req)
    {
        $dto = new QuickLinkDTO($req->validated());
        $dto->created_by = $dto->updated_by = auth()->user()->name;
        $this->authorize('create', QuickLink::class);

        return ApiResponse::success($this->quickLinkService->create($dto), 'Berhasil menambahkan data link cepat', 201);
    }

    public function update(UpdateQuickLinkRequest $req, $id)
    {
        $dto = new QuickLinkDTO($req->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->quickLinkService->update($id, $dto);
        $this->authorize('update', $result);
        return ApiResponse::success($result, 'Berhasil memperbarui data link cepat');
    }

    public function delete($id)
    {
        $model = QuickLink::findOrFail($id);
        $this->authorize('delete', $model);
        $this->quickLinkService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data link cepat');
    }
}

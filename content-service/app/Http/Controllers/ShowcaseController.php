<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateShowcaseRequest;
use App\Http\Requests\UpdateShowcaseRequest;
use App\Http\Resources\ShowcaseResource;
use App\Models\ContentDetailShowcase;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\ShowcaseDTO;
use App\Services\ShowcaseService;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    public function __construct(protected ShowcaseService $showcaseService) {}

    public function sideMenu()
    {
        $this->authorize('viewAny', ContentDetailShowcase::class);

        return ApiResponse::success($this->showcaseService->sideMenu(), 'Berhasil mengambil side menu untuk tipe showcase');
    }

    public function index(Request $request, $contentId)
    {
        $this->authorize('viewAny', ContentDetailShowcase::class);

        return ApiResponse::success($this->showcaseService->index(QueryFilterDTO::fromRequest($request), $contentId), 'Berhasil mengambil data showcase');
    }

    public function create(CreateShowcaseRequest $req)
    {
        $dto = new ShowcaseDTO($req->validated());
        $dto->created_by = $dto->updated_by = auth()->user()->name;
        $this->authorize('create', ContentDetailShowcase::class);

        return ApiResponse::success(new ShowcaseResource($this->showcaseService->create($dto)), 'Berhasil menambahkan data showcase', 201);
    }

    public function detail($id)
    {
        $result = $this->showcaseService->detail($id);
        $this->authorize('view', $result);

        return ApiResponse::success(new ShowcaseResource($result), 'Berhasil mengambil data detail showcase');
    }

    public function update(UpdateShowcaseRequest $req, $id)
    {
        $dto = new ShowcaseDTO($req->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->showcaseService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new ShowcaseResource($result), 'Berhasil memperbarui data showcase');
    }

    public function delete($id)
    {
        $model = ContentDetailShowcase::findOrFail($id);
        $this->authorize('delete', $model);
        $this->showcaseService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus showcase');
    }
}

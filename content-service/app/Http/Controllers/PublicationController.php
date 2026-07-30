<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreatePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Http\Resources\PublicationResource;
use App\Services\DTOs\PublicationDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\PublicationService;
use App\Models\ContentDetailPublication;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    public function __construct(protected PublicationService $publicationService) {}

    public function sideMenu()
    {
        $this->authorize('viewAny', ContentDetailPublication::class);
        return ApiResponse::success($this->publicationService->sideMenu(), 'Berhasil mengambil side menu untuk tipe publikasi');
    }

    public function index(Request $request, $contentId)
    {
        $this->authorize('viewAny', ContentDetailPublication::class);
        return ApiResponse::success($this->publicationService->index(QueryFilterDTO::fromRequest($request), $contentId), 'Berhasil mengambil data publikasi');
    }

    public function create(CreatePublicationRequest $req)
    {
        $dto = new PublicationDTO($req->validated());
        $dto->created_by = $dto->updated_by = auth()->user()->name;
        $this->authorize('create', ContentDetailPublication::class);

        return ApiResponse::success(new PublicationResource($this->publicationService->create($dto)), 'Berhasil menambahkan data publikasi', 201);
    }

    public function detail($id)
    {
        $result = $this->publicationService->detail($id);
        $this->authorize('view', $result);
        return ApiResponse::success(new PublicationResource($result), 'Berhasil mengambil data detail publikasi');
    }

    public function update(UpdatePublicationRequest $req, $id)
    {
        $dto = new PublicationDTO($req->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->publicationService->update($id, $dto);
        $this->authorize('update', $result);
        return ApiResponse::success(new PublicationResource($result), 'Berhasil memperbarui data publikasi');
    }

    public function delete($id)
    {
        $this->authorize('delete', ContentDetailPublication::class);
        $this->publicationService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus publikasi');
    }
}

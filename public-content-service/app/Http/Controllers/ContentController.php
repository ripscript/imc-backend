<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ContentDetailRequest;
use App\Http\Requests\ContentIndexRequest;
use App\Http\Requests\GlobalSearchRequest;
use App\Http\Resources\ContentDetailGalleryResource;
use App\Http\Resources\ContentDetailPublicationResource;
use App\Http\Resources\ContentDetailRepositoryResource;
use App\Http\Resources\ContentResource;
use App\Services\ContentService;
use App\Services\DTOs\QueryFilterDTO;

class ContentController extends Controller
{
    public function __construct(
        protected ContentService $contentService
    ) {}

    public function index(ContentIndexRequest $request)
    {
        $content = $this->contentService->index(
            $request->type,
            $request->slug,
            QueryFilterDTO::fromRequest($request)
        );

        return ApiResponse::success(new ContentResource($content), "Berhasil mengambil detail content {$request->type}");
    }

    public function detailPublication(ContentDetailRequest $request)
    {
        $publication = $this->contentService->detailPublication(
            $request->type,
            $request->content_slug,
            $request->slug
        );

        return ApiResponse::success(new ContentDetailPublicationResource($publication), 'Berhasil mengambil detail publikasi');
    }

    public function detailRepository(ContentDetailRequest $request)
    {
        $repository = $this->contentService->detailRepository(
            $request->type,
            $request->content_slug,
            $request->slug
        );

        return ApiResponse::success(new ContentDetailRepositoryResource($repository), 'Berhasil mengambil detail repositori');
    }

    public function detailGallery(ContentDetailRequest $request)
    {
        $gallery = $this->contentService->detailGallery(
            $request->type,
            $request->content_slug,
            $request->slug
        );

        return ApiResponse::success(new ContentDetailGalleryResource($gallery), 'Berhasil mengambil detail galeri');
    }

    public function globalSearch(GlobalSearchRequest $request)
    {
        $dto = new \App\Services\DTOs\GlobalSearchDTO($request->validated());
        $data = $this->contentService->globalSearch($dto->q, $dto->page, $dto->limit);

        return ApiResponse::success($data, 'Berhasil mendapatkan data pencarian');
    }
}

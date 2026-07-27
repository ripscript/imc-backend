<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\MsFaq;
use App\Services\DTOs\FaqDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\FaqService;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct(
        protected FaqService $faqService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', MsFaq::class);

        $data = $this->faqService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data FAQ');
    }

    public function detail($id)
    {
        $faq = $this->faqService->detail($id);
        $this->authorize('view', $faq);

        return ApiResponse::success(new FaqResource($faq), 'Berhasil mengambil data detail FAQ');
    }

    public function create(CreateFaqRequest $request)
    {
        $this->authorize('create', MsFaq::class);

        $dto = new FaqDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->faqService->create($dto);

        return ApiResponse::success(new FaqResource($result), 'Berhasil menambahkan data FAQ', 201);
    }

    public function update(UpdateFaqRequest $request, $id)
    {
        $dto = new FaqDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->faqService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new FaqResource($result), 'Berhasil memperbarui data FAQ');
    }

    public function destroy($id)
    {
        $this->authorize('delete', MsFaq::class);

        $this->faqService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus data FAQ');
    }
}

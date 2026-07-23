<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\UpdateAllTemplateEmailRequest;
use App\Http\Requests\UpdateTemplateEmailRequest;
use App\Services\DTOs\TemplateEmailDTO;
use App\Services\TemplateEmailService;

class TemplateEmailController extends Controller
{
    public function __construct(protected TemplateEmailService $templateEmailService) {}

    public function index()
    {
        try {
            return ApiResponse::success($this->templateEmailService->index(), 'Berhasil mengambil data template email');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            return ApiResponse::success($this->templateEmailService->detail($id), 'Berhasil mengambil data detail template email');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function update(UpdateTemplateEmailRequest $req, $id)
    {
        try {
            $dto = new TemplateEmailDTO($req->validated());
            $dto->updated_by = auth()->user()->name;

            return ApiResponse::success($this->templateEmailService->update($id, $dto), 'Berhasil memperbarui data template email');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function updateAll(UpdateAllTemplateEmailRequest $req)
    {
        try {
            $user = auth()->user()->name;
            $this->templateEmailService->updateAll($req->validated()['data'], $user);

            return ApiResponse::success('', 'Berhasil memperbarui data template email');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

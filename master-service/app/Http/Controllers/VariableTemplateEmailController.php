<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\VariableTemplateEmailResource;
use App\Services\VariableTemplateEmailService;
use Illuminate\Http\Request;

class VariableTemplateEmailController extends Controller
{
    public function __construct(
        protected VariableTemplateEmailService $variableTemplateEmailService
    ) {}

    public function index(Request $request)
    {
        $data = $this->variableTemplateEmailService->index($request);

        return ApiResponse::success(VariableTemplateEmailResource::collection($data), 'Data variable template email berhasil diambil');
    }
}

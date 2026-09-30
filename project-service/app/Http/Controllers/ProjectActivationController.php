<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ActivateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ReviewResource;
use App\Services\ProjectActivationService;
use App\Services\ProjectReviewService;

class ProjectActivationController extends Controller
{
    public function __construct(
        protected ProjectReviewService $reviewService,
        protected ProjectActivationService $activationService
    ) {}

    public function review($project_id)
    {
        return ApiResponse::success(
            new ReviewResource($this->reviewService->review((int) $project_id)),
            'Berhasil mengambil ringkasan review proyek'
        );
    }

    public function activate(ActivateProjectRequest $request, $project_id)
    {
        $user = auth('admin')->user();

        $project = $this->activationService->activate((int) $project_id, $user?->id, $user?->name);

        return ApiResponse::success(new ProjectResource($project), 'Proyek diaktivasi');
    }
}

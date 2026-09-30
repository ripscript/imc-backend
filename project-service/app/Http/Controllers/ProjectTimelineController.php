<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\SaveTimelineRequest;
use App\Services\ProjectTimelineService;

class ProjectTimelineController extends Controller
{
    public function __construct(
        protected ProjectTimelineService $timelineService
    ) {}

    public function save(SaveTimelineRequest $request, $project_id)
    {
        $result = $this->timelineService->save(
            (int) $project_id,
            $request->validated(),
            auth('admin')->user()?->name
        );

        return ApiResponse::success($result, 'Timeline proyek disimpan');
    }
}

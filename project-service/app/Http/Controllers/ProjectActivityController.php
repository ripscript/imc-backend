<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\ActivityResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ProjectActivityController extends Controller
{
    public function index(Request $request, $project_id)
    {
        $project = Project::findOrFail((int) $project_id);

        $limit = $request->input('limit') ? max((int) $request->input('limit'), 1) : 15;
        $page = max((int) $request->input('page', 1), 1);

        $query = Activity::with(['causer', 'subject'])
            ->where('log_name', 'project-management')
            ->where('properties->project_id', $project->id)
            ->orderByDesc('id');

        $total = $query->count();
        $items = $query->forPage($page, $limit)->get();

        return ApiResponse::success([
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'items' => ActivityResource::collection($items),
        ], 'Berhasil mengambil aktivitas proyek');
    }
}

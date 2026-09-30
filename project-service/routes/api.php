<?php

use App\Http\Controllers\ProjectActivationController;
use App\Http\Controllers\ProjectActivityController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectStageController;
use App\Http\Controllers\ProjectTimelineController;
use App\Http\Controllers\StageApproverController;
use App\Http\Controllers\StageDeliverableController;
use App\Http\Controllers\StageDocumentController;
use App\Http\Controllers\StageTaskController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/health', function () {
    $deps = [];
    try {
        $deps['db'] = DB::connection()->getPdo() ? 'ok' : 'error';
    } catch (\Throwable $e) {
        $deps['db'] = 'error';
    }

    return response()->json([
        'status' => 'ok',
        'message' => 'Project service berhasil diakses',
        'time' => now()->toDateTimeString(),
        'dependencies' => $deps,
    ]);
});

Route::pattern('project_id', '[0-9]+');

Route::middleware(['jwt.error:admin', 'check.role:super_admin,admin_pusat,admin_pustik'])
    ->prefix('projects')
    ->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::get('/side-menu', [ProjectController::class, 'sideMenu']);
        Route::post('/', [ProjectController::class, 'create']);
        Route::get('{project_id}/setup-state', [ProjectController::class, 'setupState']);
        Route::get('{project_id}/stages', [ProjectStageController::class, 'index']);
        Route::put('{project_id}/stages', [ProjectStageController::class, 'sync']);
        Route::put('{project_id}/stages/configuration', [ProjectStageController::class, 'configureAll']);
        Route::put('{project_id}/timeline', [ProjectTimelineController::class, 'save']);
        Route::get('{project_id}/stages/{stage_id}', [ProjectStageController::class, 'show']);
        Route::put('{project_id}/stages/{stage_id}', [ProjectStageController::class, 'update']);
        Route::get('{project_id}/stages/{stage_id}/tasks', [StageTaskController::class, 'index']);
        Route::post('{project_id}/stages/{stage_id}/tasks', [StageTaskController::class, 'store']);
        Route::put('{project_id}/stages/{stage_id}/tasks/{task_id}', [StageTaskController::class, 'update']);
        Route::delete('{project_id}/stages/{stage_id}/tasks/{task_id}', [StageTaskController::class, 'destroy']);
        Route::patch('{project_id}/tasks/{task_id}/toggle', [StageTaskController::class, 'toggle']);
        Route::get('{project_id}/stages/{stage_id}/deliverables', [StageDeliverableController::class, 'index']);
        Route::post('{project_id}/stages/{stage_id}/deliverables', [StageDeliverableController::class, 'store']);
        Route::put('{project_id}/stages/{stage_id}/deliverables/{deliverable_id}', [StageDeliverableController::class, 'update']);
        Route::delete('{project_id}/stages/{stage_id}/deliverables/{deliverable_id}', [StageDeliverableController::class, 'destroy']);
        Route::get('{project_id}/stages/{stage_id}/documents', [StageDocumentController::class, 'index']);
        Route::post('{project_id}/stages/{stage_id}/documents', [StageDocumentController::class, 'store']);
        Route::delete('{project_id}/stages/{stage_id}/documents/{document_id}', [StageDocumentController::class, 'destroy']);
        Route::get('{project_id}/stages/{stage_id}/approvers', [StageApproverController::class, 'index']);
        Route::get('{project_id}/review', [ProjectActivationController::class, 'review']);
        Route::post('{project_id}/activate', [ProjectActivationController::class, 'activate']);
        Route::get('{project_id}/activities', [ProjectActivityController::class, 'index']);
        Route::get('{project_id}/members', [ProjectMemberController::class, 'index']);
        Route::post('{project_id}/members', [ProjectMemberController::class, 'store']);
        Route::delete('{project_id}/members/{member_id}', [ProjectMemberController::class, 'destroy']);
        Route::put('{project_id}/stages/{stage_id}/approvers', [StageApproverController::class, 'sync']);
        Route::get('{project_id}', [ProjectController::class, 'detail']);
        Route::put('{project_id}', [ProjectController::class, 'update']);
        Route::delete('{project_id}', [ProjectController::class, 'destroy']);
    });

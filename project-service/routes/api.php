<?php

use App\Http\Controllers\ProjectActivationController;
use App\Http\Controllers\ProjectActivityController;
use App\Http\Controllers\ProjectChangeRequestController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectStageController;
use App\Http\Controllers\ProjectTimelineController;
use App\Http\Controllers\StageApproverController;
use App\Http\Controllers\StageDeliverableController;
use App\Http\Controllers\StageDocumentController;
use App\Http\Controllers\StageTaskController;
use App\Http\Controllers\UnifiedApprovalController;
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
Route::pattern('change_request_id', '[0-9]+');

Route::middleware(['jwt.error:admin'])
    ->prefix('approvals')
    ->group(function () {
        Route::get('/', [UnifiedApprovalController::class, 'index']);
        Route::post('{id}/approve', [UnifiedApprovalController::class, 'approve'])->whereNumber('id');
        Route::post('{id}/reject', [UnifiedApprovalController::class, 'reject'])->whereNumber('id');
    });

Route::middleware(['jwt.error:admin'])
    ->prefix('projects')
    ->group(function () {
        // Project collection
        Route::get('/', [ProjectController::class, 'index']);
        Route::get('/side-menu', [ProjectController::class, 'sideMenu']);
        Route::post('/', [ProjectController::class, 'create']);

        // Project instance scope
        Route::prefix('{project_id}')->group(function () {
            // Direct project endpoints
            Route::get('/', [ProjectController::class, 'detail']);
            Route::put('/', [ProjectController::class, 'update']);
            Route::delete('/', [ProjectController::class, 'destroy']);
            Route::get('setup-state', [ProjectController::class, 'setupState']);
            Route::put('timeline', [ProjectTimelineController::class, 'save']);
            Route::get('activities', [ProjectActivityController::class, 'index']);
            Route::get('review', [ProjectActivationController::class, 'review']);

            // Activation
            Route::prefix('activate')->group(function () {
                Route::post('/', [ProjectActivationController::class, 'activate']);
                Route::post('approve', [ProjectActivationController::class, 'approveActivation']);
                Route::post('reject', [ProjectActivationController::class, 'rejectActivation']);
            });

            // Members
            Route::prefix('members')->group(function () {
                Route::get('/', [ProjectMemberController::class, 'index']);
                Route::post('/', [ProjectMemberController::class, 'store']);
                Route::delete('{member_id}', [ProjectMemberController::class, 'destroy']);
            });

            // Change Requests
            Route::prefix('change-requests')->group(function () {
                Route::get('/', [ProjectChangeRequestController::class, 'index']);
                Route::post('/', [ProjectChangeRequestController::class, 'store']);

                Route::prefix('{change_request_id}')->group(function () {
                    Route::post('approve', [ProjectChangeRequestController::class, 'approve']);
                    Route::post('reject', [ProjectChangeRequestController::class, 'reject']);
                    Route::post('apply', [ProjectChangeRequestController::class, 'apply']);
                });
            });

            // Standalone task toggle
            Route::patch('tasks/{task_id}/toggle', [StageTaskController::class, 'toggle']);

            // Stages
            Route::prefix('stages')->group(function () {
                Route::get('/', [ProjectStageController::class, 'index']);
                Route::put('/', [ProjectStageController::class, 'sync']);
                Route::put('configuration', [ProjectStageController::class, 'configureAll']);

                // Stage instance
                Route::prefix('{stage_id}')->group(function () {
                    Route::get('/', [ProjectStageController::class, 'show']);
                    Route::put('/', [ProjectStageController::class, 'update']);
                    Route::post('complete', [ProjectStageController::class, 'complete']);
                    Route::post('approve-clearance', [ProjectStageController::class, 'complete']);
                    Route::post('request-clearance', [ProjectStageController::class, 'requestClearance']);
                    Route::post('reject-clearance', [ProjectStageController::class, 'rejectClearance']);
                    Route::post('skip', [ProjectStageController::class, 'skip']);
                    Route::post('reopen', [ProjectStageController::class, 'reopen']);

                    // Stage Approvers
                    Route::get('approvers', [StageApproverController::class, 'index']);
                    Route::put('approvers', [StageApproverController::class, 'sync']);

                    // Stage Tasks
                    Route::prefix('tasks')->group(function () {
                        Route::get('/', [StageTaskController::class, 'index']);
                        Route::post('/', [StageTaskController::class, 'store']);
                        Route::put('{task_id}', [StageTaskController::class, 'update']);
                        Route::delete('{task_id}', [StageTaskController::class, 'destroy']);
                    });

                    // Stage Deliverables
                    Route::prefix('deliverables')->group(function () {
                        Route::get('/', [StageDeliverableController::class, 'index']);
                        Route::post('/', [StageDeliverableController::class, 'store']);
                        Route::put('{deliverable_id}', [StageDeliverableController::class, 'update']);
                        Route::delete('{deliverable_id}', [StageDeliverableController::class, 'destroy']);
                    });

                    // Stage Documents
                    Route::prefix('documents')->group(function () {
                        Route::get('/', [StageDocumentController::class, 'index']);
                        Route::post('/', [StageDocumentController::class, 'store']);
                        Route::delete('{document_id}', [StageDocumentController::class, 'destroy']);
                    });
                });
            });
        });
    });

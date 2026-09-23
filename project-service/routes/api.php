<?php

use App\Http\Controllers\ProjectController;
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

Route::middleware(['jwt.error:admin', 'check.role:super_admin,admin_pusat,admin_pustik'])
    ->prefix('projects')
    ->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::get('/side-menu', [ProjectController::class, 'sideMenu']);
        Route::post('/', [ProjectController::class, 'create']);
        Route::get('{project_no}', [ProjectController::class, 'detail']);
        Route::put('{project_no}', [ProjectController::class, 'update']);
        Route::delete('{project_no}', [ProjectController::class, 'destroy']);
    });

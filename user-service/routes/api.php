<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\BulkPasswordController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiinasAuthController;
use App\Http\Controllers\UserController;
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
    try {
        \Illuminate\Support\Facades\Cache::store()->get('health-check');
        $deps['cache'] = 'ok';
    } catch (\Throwable $e) {
        $deps['cache'] = 'error';
    }
    try {
        \Illuminate\Support\Facades\Queue::size();
        $deps['queue'] = 'ok';
    } catch (\Throwable $e) {
        $deps['queue'] = 'error';
    }

    return response()->json([
        'status' => 'ok',
        'message' => 'User service berhasil diakses',
        'time' => now()->toDateTimeString(),
        'dependencies' => $deps,
    ]);
});

Route::post('auth/login', [AdminAuthController::class, 'login']);
Route::post('auth/sso/siinas', [SiinasAuthController::class, 'login']);

Route::middleware(['jwt.error:admin'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/profile', [AdminAuthController::class, 'profile']);
    });

    Route::middleware(['check.role:super_admin,admin_pusat,admin_pustik'])->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'create']);
            Route::put('{id}', [UserController::class, 'update']);
            Route::get('{id}', [UserController::class, 'detail']);
            Route::delete('{id}', [UserController::class, 'destroy']);
        });

        Route::prefix('permission')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::post('/', [PermissionController::class, 'create']);
            Route::get('{id}', [PermissionController::class, 'detail']);
            Route::put('{id}', [PermissionController::class, 'update']);
            Route::delete('{id}', [PermissionController::class, 'destroy']);
        });

        Route::prefix('role')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'create']);
            Route::get('{id}', [RoleController::class, 'detail']);
            Route::put('{id}', [RoleController::class, 'update']);
            Route::delete('{id}', [RoleController::class, 'destroy']);
        });

        Route::prefix('log')->group(function () {
            Route::get('/activity', [LogController::class, 'activity']);
        });

        Route::prefix('bulk-password')->group(function () {
            Route::get('template', [BulkPasswordController::class, 'template']);
            Route::post('import', [BulkPasswordController::class, 'import']);
        });
    });
});

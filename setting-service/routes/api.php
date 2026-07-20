<?php

use App\Http\Controllers\ChatSettingController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\TemplateEmailController;
use Illuminate\Http\Request;
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
        'status'       => 'ok',
        'message'      => 'Setting service berhasil diakses',
        'time'         => now()->toDateTimeString(),
        'dependencies' => $deps,
    ]);
});

Route::middleware(['jwt.error', 'api', 'check.role:super_admin,admin_pusat,admin_pustik,admin_satker,pimpinan'])->group(function () {
    Route::prefix('template-email')->group(function() {
        Route::get('/', [TemplateEmailController::class, 'index']);
        Route::put('update-all', [TemplateEmailController::class, 'updateAll']);
        Route::put('{id}', [TemplateEmailController::class, 'update']);
        Route::get('{id}', [TemplateEmailController::class, 'detail']);
    });

    Route::prefix('site-setting')->group(function() {
        Route::get('/', [SiteSettingController::class, 'index']);
        Route::put('/', [SiteSettingController::class, 'update']);
    });

    Route::prefix('chat-setting')->group(function() {
        Route::get('/', [ChatSettingController::class, 'index']);
        Route::put('/', [ChatSettingController::class, 'update']);
    });
});
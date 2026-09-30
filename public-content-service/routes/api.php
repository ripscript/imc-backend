<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\QuickLinkController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VisitorController;
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
        'message' => 'Public content service berhasil diakses',
        'time' => now()->toDateTimeString(),
        'dependencies' => $deps,
    ]);
});

Route::get('menu', [MenuController::class, 'index']);
Route::get('site-setting', [SettingController::class, 'index']);
Route::get('quick-link', [QuickLinkController::class, 'index']);
Route::post('visitor', [VisitorController::class, 'create']);

Route::prefix('home')->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('latest-information', [HomeController::class, 'latestInformation']);
    Route::get('statistics', [HomeController::class, 'summaryStatistic']);
    Route::get('sifat-informasi', [HomeController::class, 'sifatInformasi']);
    Route::get('{id}', [HomeController::class, 'detail']);
});

Route::prefix('content')->group(function () {
    Route::get('/', [ContentController::class, 'index']);
    Route::get('publication', [ContentController::class, 'detailPublication']);
    Route::get('repository', [ContentController::class, 'detailRepository']);
    Route::get('gallery', [ContentController::class, 'detailGallery']);
    Route::get('project', [ContentController::class, 'detailProject']);
    Route::get('showcase', [ContentController::class, 'detailShowcase']);
    Route::get('search', [ContentController::class, 'globalSearch']);
});

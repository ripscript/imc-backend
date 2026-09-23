<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ChildSubMenuController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LatestInformationController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\QuickLinkController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\ShowcaseController;
use App\Http\Controllers\SubMenuController;
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
        'message' => 'Content service berhasil diakses',
        'time' => now()->toDateTimeString(),
        'dependencies' => $deps,
    ]);
});

Route::middleware(['jwt.error', 'api', 'check.role:super_admin,admin_pusat,admin_pustik'])->group(function () {
    Route::prefix('menu')->group(function () {
        Route::get('/', [MenuController::class, 'index']);
        Route::post('/', [MenuController::class, 'create']);
        Route::get('/{id}', [MenuController::class, 'detail']);
        Route::put('/{id}', [MenuController::class, 'update']);
        Route::delete('/{id}', [MenuController::class, 'delete']);
    });

    Route::prefix('sub-menu')->group(function () {
        Route::get('/', [SubMenuController::class, 'index']);
        Route::post('/', [SubMenuController::class, 'create']);
        Route::get('/{id}', [SubMenuController::class, 'detail']);
        Route::put('/{id}', [SubMenuController::class, 'update']);
        Route::delete('/{id}', [SubMenuController::class, 'delete']);
    });

    Route::prefix('child-sub-menu')->group(function () {
        Route::get('/', [ChildSubMenuController::class, 'index']);
        Route::post('/', [ChildSubMenuController::class, 'create']);
        Route::get('/{id}', [ChildSubMenuController::class, 'detail']);
        Route::put('/{id}', [ChildSubMenuController::class, 'update']);
        Route::delete('/{id}', [ChildSubMenuController::class, 'delete']);
    });

    Route::prefix('publication')->group(function () {
        Route::get('/side-menu', [PublicationController::class, 'sideMenu']);
        Route::get('/list/{contentId}', [PublicationController::class, 'index']);
        Route::post('/', [PublicationController::class, 'create']);
        Route::get('/{id}', [PublicationController::class, 'detail']);
        Route::put('/{id}', [PublicationController::class, 'update']);
        Route::delete('/{id}', [PublicationController::class, 'delete']);
    });

    Route::prefix('gallery')->group(function () {
        Route::get('/side-menu', [GalleryController::class, 'sideMenu']);
        Route::get('/list/{contentId}', [GalleryController::class, 'index']);
        Route::post('/', [GalleryController::class, 'create']);
        Route::get('/{id}', [GalleryController::class, 'detail']);
        Route::put('/{id}', [GalleryController::class, 'update']);
        Route::delete('/{id}', [GalleryController::class, 'delete']);
    });

    Route::prefix('repository')->group(function () {
        Route::get('/side-menu', [RepositoryController::class, 'sideMenu']);
        Route::get('/list/{contentId}', [RepositoryController::class, 'index']);
        Route::post('/', [RepositoryController::class, 'create']);
        Route::get('/{id}', [RepositoryController::class, 'detail']);
        Route::put('/{id}', [RepositoryController::class, 'update']);
        Route::delete('/{id}', [RepositoryController::class, 'delete']);
    });

    Route::prefix('agenda')->group(function () {
        Route::get('/side-menu', [AgendaController::class, 'sideMenu']);
        Route::get('/list/{contentId}', [AgendaController::class, 'index']);
        Route::post('/', [AgendaController::class, 'create']);
        Route::get('/{id}', [AgendaController::class, 'detail']);
        Route::put('/{id}', [AgendaController::class, 'update']);
        Route::delete('/{id}', [AgendaController::class, 'delete']);
    });

    Route::prefix('showcase')->group(function () {
        Route::get('/side-menu', [ShowcaseController::class, 'sideMenu']);
        Route::get('/list/{contentId}', [ShowcaseController::class, 'index']);
        Route::post('/', [ShowcaseController::class, 'create']);
        Route::get('/{id}', [ShowcaseController::class, 'detail']);
        Route::put('/{id}', [ShowcaseController::class, 'update']);
        Route::delete('/{id}', [ShowcaseController::class, 'delete']);
    });

    Route::prefix('section')->group(function () {
        Route::get('/', [HomeController::class, 'index']);
        Route::post('/', [HomeController::class, 'create']);
        Route::get('/{id}', [HomeController::class, 'detail']);
        Route::put('/{id}', [HomeController::class, 'update']);
        Route::delete('/{id}', [HomeController::class, 'delete']);
    });

    Route::prefix('latest-information')->group(function () {
        Route::get('/', [LatestInformationController::class, 'index']);
        Route::post('/', [LatestInformationController::class, 'create']);
        Route::get('/{id}', [LatestInformationController::class, 'detail']);
        Route::put('/{id}', [LatestInformationController::class, 'update']);
        Route::delete('/{id}', [LatestInformationController::class, 'delete']);
    });

    Route::prefix('quick-link')->group(function () {
        Route::get('/', [QuickLinkController::class, 'index']);
        Route::post('/', [QuickLinkController::class, 'create']);
        Route::get('/{id}', [QuickLinkController::class, 'detail']);
        Route::put('/{id}', [QuickLinkController::class, 'update']);
        Route::delete('/{id}', [QuickLinkController::class, 'delete']);
    });

    Route::get('content', [ContentController::class, 'index']);

    Route::prefix('dashboard')->group(function () {
        Route::get('jumlah-perangkat', [DashboardController::class, 'jumlahPerangkat']);
        Route::get('grafik-pengunjung', [DashboardController::class, 'grafikPengunjung']);
    });
});

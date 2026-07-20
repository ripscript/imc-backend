<?php

use App\Http\Controllers\AdminKeberatanController;
use App\Http\Controllers\AdminPermohonanController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeberatanController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\SurveyKepuasanController;
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
    return response()->json([
        'status'  => 'ok',
        'message' => 'Permohonan service berhasil diakses',
        'time'    => now()->toDateTimeString(),
    ]);
});

Route::middleware(['jwt.error:pemohon'])->group(function () {
    Route::prefix('permohonan')->group(function() {
        Route::get('/', [PermohonanController::class, 'index']);
        Route::post('/', [PermohonanController::class, 'create']);
        Route::get('/detail', [PermohonanController::class, 'detail']);
        Route::put('/update', [PermohonanController::class, 'update']);
        Route::post('/verify', [PermohonanController::class, 'verify']);
        Route::delete('/delete', [PermohonanController::class, 'delete']);
        Route::get('/setting', [PermohonanController::class, 'setting']);
        Route::get('/track', [PermohonanController::class, 'track']);
    });

    Route::prefix('keberatan')->group(function() {
        Route::get('/', [KeberatanController::class, 'index']);
        Route::post('/', [KeberatanController::class, 'create']);
        Route::get('/detail', [KeberatanController::class, 'detail']);
        Route::put('/update', [KeberatanController::class, 'update']);
        Route::delete('/delete', [KeberatanController::class, 'delete']);
    });

    Route::post('/survey-kepuasan', [SurveyKepuasanController::class, 'create']);
});

Route::middleware(['jwt.error:admin'])->group(function () {
    Route::prefix('admin')->group(function() {
        Route::prefix('permohonan')->group(function() {
            Route::get('/', [AdminPermohonanController::class, 'index']);
            Route::get('/export', [AdminPermohonanController::class, 'export']);
            Route::get('/{id}', [AdminPermohonanController::class, 'detail']);
            Route::put('/{id}', [AdminPermohonanController::class, 'update']);
            Route::put('disposition/{id}', [AdminPermohonanController::class, 'disposition']);
            Route::post('additional-time', [AdminPermohonanController::class, 'additionalTime']);
            Route::delete('/{id}', [AdminPermohonanController::class, 'delete']);
        });

        Route::prefix('keberatan')->group(function() {
            Route::get('/', [AdminKeberatanController::class, 'index']);
            Route::get('/export', [AdminKeberatanController::class, 'export']);
            Route::get('/{id}', [AdminKeberatanController::class, 'detail']);
            Route::put('/{id}', [AdminKeberatanController::class, 'update']);
        });

        Route::prefix('dashboard')->group(function() {
            Route::get('jumlah-permohonan-keberatan', [DashboardController::class, 'jumlahPermohonanKeberatan']);
            Route::get('grafik-permohonan-by-tipe-pemohon', [DashboardController::class, 'grafikPermohonanByTipePemohon']);
            Route::get('grafik-permohonan-perbulan', [DashboardController::class, 'grafikPermohonanPerBulan']);
            Route::get('satker-sla-terlambat', [DashboardController::class, 'satkerSlaTerlambat']);
            Route::get('perwakilan-sla-terlambat', [DashboardController::class, 'perwakilanSlaTerlambat']);
            Route::get('permohonan-akan-kadaluwarsa', [DashboardController::class, 'permohonanAkanKadaluwarsa']);
            Route::get('jumlah-permohonan-akan-kadaluwarsa', [DashboardController::class, 'jumlahPermohonanAkanKadaluwarsa']);
            Route::get('permohonan-tambahan-waktu', [DashboardController::class, 'permohonanDenganTambahanWaktu']);
            Route::get('export', [DashboardController::class, 'export']);
        });

        Route::prefix('diskusi')->group(function() {
            Route::get('/', [CommentController::class, 'index']);
            Route::post('/', [CommentController::class, 'create']);
            Route::put('/{id}', [CommentController::class, 'update']);
            Route::delete('/{id}', [CommentController::class, 'delete']);
        });

        Route::prefix('survey-kepuasan')->middleware(['check.role:super_admin,admin_pusat,admin_pustik'])->group(function() {
            Route::get('/', [SurveyKepuasanController::class, 'index']);
            Route::get('/avg-nilai', [SurveyKepuasanController::class, 'avgNilai']);
            Route::get('/export', [SurveyKepuasanController::class, 'export']);
            Route::get('/{id}', [SurveyKepuasanController::class, 'detail']);
        });
    });
});

Route::get('/permohonan/update-late-status', [AdminPermohonanController::class, 'updateLateStatus']);
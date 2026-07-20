<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\BulkPasswordController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PemohonAuthController;
use App\Http\Controllers\PemohonController;
use App\Http\Controllers\UserController;
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
        'message' => 'User service berhasil diakses',
        'time'    => now()->toDateTimeString(),
    ]);
});

Route::post('auth/login', [AdminAuthController::class, 'login']);

Route::middleware(['jwt.error:admin'])->group(function () {
    Route::prefix('auth')->group(function() {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/profile', [AdminAuthController::class, 'profile']); 
    });

    Route::middleware(['check.role:super_admin,admin_pusat,admin_pustik'])->group(function () {
        Route::prefix('user')->group(function() {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'create']);
            Route::put('{id}', [UserController::class, 'update']);
            Route::get('{id}', [UserController::class, 'detail']);
            Route::delete('{id}', [UserController::class, 'destroy']);
        });

        Route::prefix('management-pemohon')->group(function() {
            Route::get('/', [PemohonController::class, 'index']);
            Route::put('{id}', [PemohonController::class, 'updateByAdmin']);
            Route::get('{id}', [PemohonController::class, 'detail']);
            Route::post('resend-verification/{id}', [PemohonController::class, 'resendVerification']);
            Route::delete('delete/{id}', [PemohonController::class, 'delete']);
        });

        Route::prefix('log')->group(function() {
            Route::get('/activity', [LogController::class, 'activity']);
            Route::get('/chat', [LogController::class, 'chat']);
            Route::get('/chat/{session}', [LogController::class, 'chatDetail']);
        });

        Route::prefix('bulk-password')->group(function() {
            Route::get('template', [BulkPasswordController::class, 'template']);
            Route::post('import', [BulkPasswordController::class, 'import']);
        });
    });
});

// Pemohon
Route::prefix('pemohon')->group(function() {
    Route::prefix('auth')->group(function() {
        Route::post('/login', [PemohonAuthController::class, 'login']);
        Route::post('/register', [PemohonAuthController::class, 'register']);
        Route::post('/forgot-password', [PemohonAuthController::class, 'sendResetLinkEmail']);
        Route::post('/reset-password', [PemohonAuthController::class, 'resetPassword']);
        Route::post('/verify-email', [PemohonAuthController::class, 'verifyEmail']);
        Route::get('/verify-email', [PemohonAuthController::class, 'checkVerifyEmail']);
        Route::get('/resend-otp', [PemohonAuthController::class, 'resendOtp']);
    });

    Route::middleware(['jwt.error:pemohon'])->group(function () {
        Route::prefix('auth')->group(function() {
            Route::post('/logout', [PemohonAuthController::class, 'logout']);
            Route::get('/profile', [PemohonAuthController::class, 'profile']); 
        });

        Route::put('/profile', [PemohonController::class, 'updateByPemohon']);
    });
});
<?php

use App\Http\Controllers\FileController;
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
        'message' => 'File service berhasil diakses',
        'time'    => now()->toDateTimeString(),
    ]);
});

Route::get('show', [FileController::class, 'show']);
Route::get('download', [FileController::class, 'download']);
Route::post('base64', [FileController::class, 'getFileBase64']);

Route::middleware(['jwt.error:admin,pemohon'])->group(function () {
    Route::post('upload', [FileController::class, 'upload']);
    Route::post('compress-image', [FileController::class, 'compressImage']);
    Route::post('lock-pdf', [FileController::class, 'lockUploadedPdf']);
    Route::post('lock-image', [FileController::class, 'lockUploadedImage']);
});


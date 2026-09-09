<?php

use App\Http\Controllers\AlasanKeberatanController;
use App\Http\Controllers\ClassificationPemohonController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\KategoriContentController;
use App\Http\Controllers\KategoriFaqController;
use App\Http\Controllers\LayoutController;
use App\Http\Controllers\NegaraController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\PerwakilanController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProjectCategoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SatuanKerjaController;
use App\Http\Controllers\SifatInformasiController;
use App\Http\Controllers\SurveyQuestionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\VariableTemplateEmailController;
use App\Http\Controllers\WorkflowController;
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
        'message' => 'Master service berhasil diakses',
        'time' => now()->toDateTimeString(),
        'dependencies' => $deps,
    ]);
});

Route::middleware(['jwt.error:admin', 'check.role:super_admin,admin_pusat,admin_pustik'])->group(function () {
    Route::prefix('negara')->group(function () {
        Route::get('/', [NegaraController::class, 'index']);
        Route::post('/', [NegaraController::class, 'create']);
        Route::put('{id}', [NegaraController::class, 'update']);
        Route::get('{id}', [NegaraController::class, 'detail']);
        Route::delete('{id}', [NegaraController::class, 'destroy']);
    });

    Route::prefix('perwakilan')->group(function () {
        Route::post('/', [PerwakilanController::class, 'create']);
        Route::put('{id}', [PerwakilanController::class, 'update']);
        Route::get('{id}', [PerwakilanController::class, 'detail']);
        Route::delete('{id}', [PerwakilanController::class, 'destroy']);
    });

    Route::prefix('sifat-informasi')->group(function () {
        Route::get('/', [SifatInformasiController::class, 'index']);
        Route::post('/', [SifatInformasiController::class, 'create']);
        Route::put('{id}', [SifatInformasiController::class, 'update']);
        Route::get('{id}', [SifatInformasiController::class, 'detail']);
        Route::delete('{id}', [SifatInformasiController::class, 'destroy']);
    });

    Route::prefix('kategori-faq')->group(function () {
        Route::get('/', [KategoriFaqController::class, 'index']);
        Route::post('/', [KategoriFaqController::class, 'create']);
        Route::put('{id}', [KategoriFaqController::class, 'update']);
        Route::get('{id}', [KategoriFaqController::class, 'detail']);
        Route::delete('{id}', [KategoriFaqController::class, 'destroy']);
    });

    Route::prefix('faq')->group(function () {
        Route::get('/', [FaqController::class, 'index']);
        Route::post('/', [FaqController::class, 'create']);
        Route::put('{id}', [FaqController::class, 'update']);
        Route::get('{id}', [FaqController::class, 'detail']);
        Route::delete('{id}', [FaqController::class, 'destroy']);
    });

    Route::prefix('satuan-kerja')->group(function () {
        Route::post('/', [SatuanKerjaController::class, 'create']);
        Route::put('{id}', [SatuanKerjaController::class, 'update']);
        Route::get('{id}', [SatuanKerjaController::class, 'detail']);
        Route::delete('{id}', [SatuanKerjaController::class, 'destroy']);
        Route::get('sub/{id}', [SatuanKerjaController::class, 'getSub']);
    });

    Route::prefix('kategori-content')->group(function () {
        Route::get('/', [KategoriContentController::class, 'index']);
        Route::post('/', [KategoriContentController::class, 'create']);
        Route::put('{id}', [KategoriContentController::class, 'update']);
        Route::get('{id}', [KategoriContentController::class, 'detail']);
        Route::delete('{id}', [KategoriContentController::class, 'destroy']);
    });

    Route::prefix('tag')->group(function () {
        Route::get('/', [TagController::class, 'index']);
        Route::post('/', [TagController::class, 'create']);
        Route::put('{id}', [TagController::class, 'update']);
        Route::get('{id}', [TagController::class, 'detail']);
        Route::delete('{id}', [TagController::class, 'destroy']);
    });

    Route::prefix('alasan-keberatan')->group(function () {
        Route::post('/', [AlasanKeberatanController::class, 'create']);
        Route::put('{id}', [AlasanKeberatanController::class, 'update']);
        Route::get('{id}', [AlasanKeberatanController::class, 'detail']);
        // Route::delete('{id}', [AlasanKeberatanController::class, 'destroy']);
    });

    Route::prefix('hari-libur')->group(function () {
        Route::get('/', [HolidayController::class, 'index']);
        Route::post('/', [HolidayController::class, 'create']);
        Route::get('template-import', [HolidayController::class, 'templateImport']);
        Route::post('import', [HolidayController::class, 'import']);
        Route::put('{id}', [HolidayController::class, 'update']);
        Route::get('{id}', [HolidayController::class, 'detail']);
        Route::delete('{id}', [HolidayController::class, 'destroy']);
    });

    Route::prefix('survey-question')->group(function () {
        Route::get('/', [SurveyQuestionController::class, 'index']);
        Route::post('/', [SurveyQuestionController::class, 'create']);
        Route::put('{id}', [SurveyQuestionController::class, 'update']);
        Route::get('{id}', [SurveyQuestionController::class, 'detail']);
        Route::delete('{id}', [SurveyQuestionController::class, 'destroy']);
    });

    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'create']);
        Route::put('{id}', [ClientController::class, 'update']);
        Route::get('{id}', [ClientController::class, 'detail']);
        Route::delete('{id}', [ClientController::class, 'destroy']);
    });

    Route::prefix('partners')->group(function () {
        Route::get('/', [PartnerController::class, 'index']);
        Route::post('/', [PartnerController::class, 'create']);
        Route::put('{id}', [PartnerController::class, 'update']);
        Route::get('{id}', [PartnerController::class, 'detail']);
        Route::delete('{id}', [PartnerController::class, 'destroy']);
    });

    Route::prefix('project-categories')->group(function () {
        Route::get('/', [ProjectCategoryController::class, 'index']);
        Route::post('/', [ProjectCategoryController::class, 'create']);
        Route::put('{id}', [ProjectCategoryController::class, 'update']);
        Route::get('{id}', [ProjectCategoryController::class, 'detail']);
        Route::delete('{id}', [ProjectCategoryController::class, 'destroy']);
    });

    Route::prefix('document-types')->group(function () {
        Route::get('/', [DocumentTypeController::class, 'index']);
        Route::post('/', [DocumentTypeController::class, 'create']);
        Route::put('{id}', [DocumentTypeController::class, 'update']);
        Route::get('{id}', [DocumentTypeController::class, 'detail']);
        Route::delete('{id}', [DocumentTypeController::class, 'destroy']);
    });

    Route::prefix('workflows')->group(function () {
        Route::get('/', [WorkflowController::class, 'index']);
        Route::post('/', [WorkflowController::class, 'create']);
        Route::put('{id}', [WorkflowController::class, 'update']);
        Route::get('{id}', [WorkflowController::class, 'detail']);
        Route::delete('{id}', [WorkflowController::class, 'destroy']);
        Route::get('{id}/stages', [WorkflowController::class, 'stages']);
    });

    Route::prefix('custom-fields')->group(function () {
        Route::get('/', [CustomFieldController::class, 'index']);
        Route::post('/', [CustomFieldController::class, 'create']);
        Route::put('{id}', [CustomFieldController::class, 'update']);
        Route::get('{id}', [CustomFieldController::class, 'detail']);
        Route::delete('{id}', [CustomFieldController::class, 'destroy']);
    });

    Route::get('custom-field-values', [CustomFieldController::class, 'values']);
    Route::put('custom-field-values', [CustomFieldController::class, 'upsertValues']);

    Route::get('role', [RoleController::class, 'index']);
    Route::get('variable-template-email', [VariableTemplateEmailController::class, 'index']);
    Route::get('layout', [LayoutController::class, 'index']);
});

Route::middleware(['jwt.error:admin,pemohon'])->group(function () {
    Route::prefix('permohonan')->group(function () {
        Route::get('akses-data', [PermohonanController::class, 'aksesData']);
        Route::get('format-data', [PermohonanController::class, 'formatData']);
        Route::get('salinan-informasi', [PermohonanController::class, 'salinanInformasi']);
    });

    Route::get('satuan-kerja', [SatuanKerjaController::class, 'index']);
    Route::get('perwakilan', [PerwakilanController::class, 'index']);
    Route::get('alasan-keberatan', [AlasanKeberatanController::class, 'index']);
    Route::get('list-pertanyaan-survei', [SurveyQuestionController::class, 'listActive']);
});

Route::get('classification-pemohon', [ClassificationPemohonController::class, 'index']);

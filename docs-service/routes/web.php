<?php

use App\Http\Controllers\DocsController;
use App\Http\Controllers\SpecController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DocsController::class, 'landing']);
Route::get('/specs/{service}.json', [SpecController::class, 'show'])->name('specs.show');

<?php

use App\Http\Controllers\ValidatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ValidatorController::class, 'index'])->name('validator');
Route::post('/validate', [ValidatorController::class, 'validate'])->name('validate');

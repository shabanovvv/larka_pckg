<?php

use Illuminate\Support\Facades\Route;
use Salavey\AiCodeReviewer\Controllers\AiController;

Route::prefix('ai')->name('ai.')->group(function () {
    Route::get('/', [AiController::class, 'index'])->name('index');
});
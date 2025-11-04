<?php

use Notifier\Auth\Infrastructure\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterController::class);
    
});
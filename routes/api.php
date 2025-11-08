<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController;

Route::post('/login', [AuthController::class, 'login']);

// routes protected by sanctum token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin routes
    Route::middleware('check.role:admin')->prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class);
        // nanti tambahkan admin endpoints lain (storages, bintex, etc)
    });

    // contoh route untuk editor/editor+admin
    // Route::middleware('check.role:admin|editor')->post('/something', ...);
});

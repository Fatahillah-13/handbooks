<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\Content\StorageController;
use App\Http\Controllers\Api\Admin\Content\BintexController;
use App\Http\Controllers\Api\Admin\Content\DocumentController;
use App\Http\Controllers\Api\Public\ViewerController;
use App\Http\Controllers\Api\Admin\AuditLogController;

// Route::post('/login', [AuthController::class, 'login']);

// Route for viewer
Route::get('/viewer/{document}', [ViewerController::class, 'show']);
Route::get('/viewer/page/{page}', [ViewerController::class, 'page'])
    ->name('viewer.page')->middleware('signed');

// routes protected by sanctum token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin routes
    Route::middleware('check.role:admin')->prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        // nanti tambahkan admin endpoints lain (storages, bintex, etc)
    });

    Route::middleware('check.role:admin|editor|viewer')->prefix('admin')->group(function () {
        Route::apiResource('storages', StorageController::class);
        Route::apiResource('bintexes', BintexController::class);
        Route::apiResource('documents', DocumentController::class);
    });



    // contoh route untuk editor/editor+admin
    // Route::middleware('check.role:admin|editor')->post('/something', ...);
});

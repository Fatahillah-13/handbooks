<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'spaLogin'])->name('login');
Route::post('/logout', [AuthController::class, 'spaLogout'])->middleware('auth');


Route::view('/{any}', 'app')->where('any', '.*');
// atau kalau mau eksplisit:
// Route::get('/{any}', fn () => view('app'))->where('any', '.*');

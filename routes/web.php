<?php

use Illuminate\Support\Facades\Route;

Route::view('/{any}', 'app')->where('any', '.*');
// atau kalau mau eksplisit:
// Route::get('/{any}', fn () => view('app'))->where('any', '.*');

<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
});

// routes/web.php
Route::get('/test-gs', function () {
    $output = [];
    $returnVar = 0;

    exec('gswin64c -version 2>&1', $output, $returnVar); // atau 'gs -version'
    dd($output, $returnVar);
});

<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/login', function () {
    return Inertia::render('Login');   // resources/js/Pages/Login.vue
})->name('login');

Route::get('/storage/{slug}', function ($slug) {
    return Inertia::render('StorageView', [  // 👈 HARUS persis "StorageView"
        'slug' => $slug,
    ]);
})->name('storage.show');

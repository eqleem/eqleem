<?php

use Illuminate\Support\Facades\Route;

// Serve the Vue SPA for /dashboard and any client-side (Vue Router) sub-path.
Route::get('/dashboard/{any?}', function () {
    return view('dashboard');
})->where('any', '.*')->middleware('auth:sanctum')->name('dashboard');

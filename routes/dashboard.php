<?php

use App\Actions\DashboardPlanPaymentCallback;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/payments/moyasar/callback', DashboardPlanPaymentCallback::class)
    ->middleware('auth:sanctum')
    ->name('dashboard.payments.moyasar.callback');

// Serve the Vue SPA for /dashboard and any client-side (Vue Router) sub-path.
Route::get('/dashboard/{any?}', function () {
    return view('dashboard');
})->where('any', '.*')->middleware('auth:sanctum')->name('dashboard');

<?php

use App\Actions\PaymentCallback;
use App\Actions\UploadImage;
use App\Actions\UploadMedia;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// admin
// Route::as('admin.')
//     ->prefix('admin')
//     ->middleware(['auth', 'admin'])
//     ->group(function () {
//         Route::livewire('/', 'admin::home')->name('home');
//         Route::livewire('/plan', 'admin::plan.home')->name('plan.home');
//         Route::livewire('/plan/{plan}/checkout', 'admin::plan.checkout')->name('plan.checkout');
//         Route::get('/payments/moyasar/callback', PaymentCallback::class)->name('payments.moyasar.callback');
//         Route::livewire('/settings', 'admin::settings.home')->name('settings.home');
//         Route::livewire('/settings/{slug}', 'admin::settings.detail')->name('settings.detail');
//         Route::livewire('/account', 'admin::account.home')->name('account.home');
//         Route::livewire('/orders', 'admin::orders.home')->name('orders.home');
//         Route::livewire('/orders/payments/{uuid}', 'admin::orders.payment-detail')->name('orders.payments.detail');
//         Route::livewire('/orders/invoices/{uuid}', 'admin::orders.invoice-detail')->name('orders.invoices.detail');
//         Route::livewire('/orders/form-submissions/{id}', 'admin::orders.form-submission-detail')->name('orders.form-submissions.detail');
//         Route::livewire('/orders/{id}', 'admin::orders.detail')->name('orders.detail');
//         Route::livewire('/clients', 'admin::clients.home')->name('clients.home');
//         Route::livewire('/clients/{id}', 'admin::clients.detail')->name('clients.detail');
//         Route::livewire('/manage-page', 'admin::page.home')->name('page.home');
//         Route::post('upload-media', [UploadMedia::class, 'upload'])->name('upload-media');
//         Route::post('upload-image', [UploadImage::class, 'upload'])->name('upload-image');
//     });

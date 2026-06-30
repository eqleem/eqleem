<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// admin
Route::as('admin.')
    ->prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::livewire('/', 'admin::home')->name('home');
        Route::livewire('/plan', 'admin::plan.home')->name('plan.home');
        Route::livewire('/settings', 'admin::settings.home')->name('settings.home');
        Route::livewire('/settings/{slug}', 'admin::settings.detail')->name('settings.detail');
        Route::livewire('/account', 'admin::account.home')->name('account.home');
        Route::livewire('/orders', 'admin::orders.home')->name('orders.home');
        Route::livewire('/clients', 'admin::clients.home')->name('clients.home');
        Route::livewire('/manage-page', 'admin::page.home')->name('page.home');
        // Route::post('upload-media', [\App\Actions\UploadMedia::class, 'upload'])->name('upload-media');
        // Route::post('upload-image', [\App\Actions\UploadImage::class, 'upload'])->name('upload-image');
        // Route::middleware(['web'])
        //     ->group(function () {
        //         Volt::route('/content', 'admin.content.index')->name('content');
        //         Volt::route('/design', 'admin.design.index')->name('design');
        //         Volt::route('/share', 'admin.share')->name('share');
        //     });

        // // orders
        // Route::as('orders.')
        //     ->prefix('orders')
        //     ->middleware(['web'])
        //     ->group(function () {
        //         Volt::route('/', 'admin.orders.index')->name('index');
        //         Volt::route('/{id}', 'admin.orders.detail')->name('detail');
        //     });

        // Route::as('account.')
        // ->prefix('account')
        // ->group(function () {
        //     Volt::route('/', 'admin.account.index')->name('index');
        //     Volt::route('/tenants', 'admin.account.tenants')->name('tenants');
        // });

        // Route::as('subscription.')
        // ->prefix('subscription')
        // ->group(function () {
        //     Volt::route('/', 'admin.subscription.index')->name('index');
        //     Volt::route('/confirm-subscription', 'admin.subscription.confirm-subscription')->name('confirm-subscription');
        // });

        // Route::as('settings.')
        //     ->prefix('settings')
        //     ->group(function () {
        //         Volt::route('/', 'admin.settings.index')->name('index');
        //         Volt::route('/{slug}', 'admin.settings.detail')->name('detail');
        //     });

    });

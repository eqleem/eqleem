<?php

use App\Http\Middleware\ResolveTenantFromPath;
use Illuminate\Support\Facades\Route;

Route::prefix('{tenant}')
    ->middleware(ResolveTenantFromPath::class)
    ->as('tenant.')
    ->group(function () {
        Route::livewire('/', 'tenant::home')->name('home');
        Route::livewire('/store', 'tenant::store.index')->name('store.index');
        Route::livewire('/store/product/{slug}', 'tenant::store.detail')->name('store.detail');
        Route::livewire('/properties-rental', 'tenant::properties-rental.index')->name('properties-rental.index');
        Route::livewire('/properties-rental/{slug}', 'tenant::properties-rental.detail')->name('properties-rental.detail');
        Route::livewire('/properties', 'tenant::properties.index')->name('properties.index');
        Route::livewire('/properties/{slug}', 'tenant::properties.detail')->name('properties.detail');

        Route::livewire('/courses', 'tenant::courses.index')->name('courses.index');
        Route::livewire('/courses/{slug}', 'tenant::courses.detail')->name('courses.detail');
        Route::livewire('/playlists', 'tenant::playlists.index')->name('playlists.index');
        Route::livewire('/playlists/{slug}', 'tenant::playlists.detail')->name('playlists.detail');
        Route::livewire('/menu', 'tenant::menu.index')->name('menu.index');
        Route::livewire('/services', 'tenant::services.index')->name('services.index');
        Route::livewire('/services/{slug}', 'tenant::services.detail')->name('services.detail');
        Route::livewire('/branches', 'tenant::pages.branches')->name('pages.branches');
        Route::livewire('/changelog', 'tenant::pages.changelog')->name('pages.changelog');
        Route::livewire('/resume', 'tenant::pages.resume')->name('pages.resume');
        Route::livewire('/reviews', 'tenant::pages.reviews')->name('pages.reviews');
        Route::livewire('/faq', 'tenant::pages.faq')->name('pages.faq');
        Route::livewire('/features', 'tenant::pages.features')->name('pages.features');
        Route::livewire('/pricing', 'tenant::pages.pricing')->name('pages.pricing');
        Route::livewire('/contact', 'tenant::pages.contact')->name('pages.contact');
        Route::livewire('/checkout', 'tenant::pages.checkout')->name('pages.checkout');
        Route::livewire('/cart', 'tenant::pages.cart')->name('pages.cart');

        // Route::livewire('/pages/{slug}', 'tenant::pages.detail')->name('pages.detail');
        Route::livewire('/portfolio', 'tenant::portfolio.index')->name('portfolio.index');
        Route::livewire('/portfolio/{slug}', 'tenant::portfolio.detail')->name('portfolio.detail');
        Route::livewire('/blog', 'tenant::blog.index')->name('blog.index');
        Route::livewire('/blog/{slug}', 'tenant::blog.detail')->name('blog.detail');
        Route::livewire('/newsletter', 'tenant::newsletter.index')->name('newsletter.index');
        Route::livewire('/newsletter/{slug}', 'tenant::newsletter.detail')->name('newsletter.detail');
    });

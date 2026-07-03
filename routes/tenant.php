<?php

use App\Http\Middleware\ResolveTenantFromPath;
use App\Http\Middleware\SetTenantTheme;
use Illuminate\Support\Facades\Route;
use Pages\Branches;
use Pages\Cart;
use Pages\Changelog;
use Pages\Checkout;
use Pages\Contact;
use Pages\Faq;
use Pages\Features;
use Pages\Pricing;
use Pages\Resume;
use Pages\Reviews;
use Store\Detail;
use Store\Index;

Route::prefix('{tenant}')
    ->middleware(ResolveTenantFromPath::class, SetTenantTheme::class)
    ->as('tenant.')
    ->namespace('App\Livewire\Tenant')
    ->group(function () {
        Route::get('/', Home::class)->name('home');

        Route::get('/store', Index::class)->name('store.index');
        Route::get('/store/product/{slug}', Detail::class)->name('store.detail');

        Route::get('/properties-rental', PropertiesRental\Index::class)->name('properties-rental.index');
        Route::get('/properties-rental/{slug}', PropertiesRental\Detail::class)->name('properties-rental.detail');

        Route::get('/properties', Properties\Index::class)->name('properties.index');
        Route::get('/properties/{slug}', Properties\Detail::class)->name('properties.detail');

        Route::get('/courses', Courses\Index::class)->name('courses.index');
        Route::get('/courses/{slug}', Courses\Detail::class)->name('courses.detail');

        Route::get('/playlists', Playlists\Index::class)->name('playlists.index');
        Route::get('/playlists/{slug}', Playlists\Detail::class)->name('playlists.detail');

        Route::get('/menu', Menu\Index::class)->name('menu.index');

        Route::get('/services', Services\Index::class)->name('services.index');
        Route::get('/services/{slug}', Services\Detail::class)->name('services.detail');

        Route::get('/portfolio', Portfolio\Index::class)->name('portfolio.index');
        Route::get('/portfolio/{slug}', Portfolio\Detail::class)->name('portfolio.detail');

        Route::get('/blog', Blog\Index::class)->name('blog.index');
        Route::get('/blog/{slug}', Blog\Detail::class)->name('blog.detail');

        Route::get('/pages/{slug}', Page\Detail::class)->name('page.detail');

        Route::get('/newsletter', Newsletter\Index::class)->name('newsletter.index');
        Route::get('/newsletter/{slug}', Newsletter\Detail::class)->name('newsletter.detail');

        Route::get('/branches', Branches::class)->name('pages.branches');
        Route::get('/changelog', Changelog::class)->name('pages.changelog');
        Route::get('/resume', Resume::class)->name('pages.resume');
        Route::get('/reviews', Reviews::class)->name('pages.reviews');
        Route::get('/faq', Faq::class)->name('pages.faq');
        Route::get('/features', Features::class)->name('pages.features');
        Route::get('/pricing', Pricing::class)->name('pages.pricing');
        Route::get('/contact', Contact::class)->name('pages.contact');
        Route::get('/checkout', Checkout::class)->name('pages.checkout');
        Route::get('/cart', Cart::class)->name('pages.cart');
    });

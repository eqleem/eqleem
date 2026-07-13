<?php

use App\Support\ContentType;
use App\Support\ContentTypeRegistry;

it('returns only active content types from config', function () {
    config([
        'content-types' => [
            'blog' => [
                'order' => 1,
                'slug' => 'blog',
                'model_type' => 'blog',
                'name' => 'المدونة',
                'description' => 'Blog',
                'icon' => 'assets/icons/stationery/002-book.svg',
                'color' => 'orange',
            ],
            'courses' => [
                'active' => false,
                'order' => 2,
                'slug' => 'courses',
                'model_type' => 'course',
                'name' => 'الدورات',
                'description' => 'Courses',
                'icon' => 'assets/icons/business/011-presentation.svg',
                'color' => 'pink',
            ],
            'store' => [
                'active' => true,
                'order' => 3,
                'slug' => 'store',
                'model_type' => 'product',
                'name' => 'المتجر',
                'description' => 'Store',
                'icon' => 'assets/icons/ecommerce/018-cart.svg',
                'color' => 'green',
            ],
        ],
    ]);

    $registry = app(ContentTypeRegistry::class);

    expect($registry->all()->pluck('slug')->all())->toBe(['blog', 'store'])
        ->and($registry->configured()->pluck('slug')->all())->toBe(['blog', 'courses', 'store'])
        ->and($registry->findActive('courses'))->toBeNull()
        ->and($registry->find('courses'))->toBeInstanceOf(ContentType::class)
        ->and($registry->find('courses')?->active)->toBeFalse();
});

it('builds nav tabs for active content types only', function () {
    config([
        'content-types' => [
            'portfolio' => [
                'order' => 1,
                'slug' => 'portfolio',
                'model_type' => 'portfolio',
                'name' => 'معرض الأعمال',
                'description' => 'Portfolio',
                'icon' => 'assets/icons/business/047-portfolio.svg',
                'color' => 'violet',
            ],
            'menu' => [
                'active' => false,
                'order' => 2,
                'slug' => 'menu',
                'model_type' => 'menu',
                'name' => 'قائمة الطعام',
                'description' => 'Menu',
                'icon' => 'assets/icons/business/059-teacup.svg',
                'color' => 'amber',
            ],
        ],
    ]);

    $tabs = app(ContentTypeRegistry::class)->tabs();

    expect($tabs)->toHaveCount(1)
        ->and($tabs[0]['slug'])->toBe('portfolio')
        ->and($tabs[0]['id'])->toBe('content-portfolio')
        ->and($tabs[0]['content_type']['slug'])->toBe('portfolio')
        ->and($tabs[0]['sellable'])->toBeFalse();
});

it('exposes sellable flag from config on tabs', function () {
    config([
        'content-types' => [
            'blog' => [
                'order' => 1,
                'slug' => 'blog',
                'model_type' => 'blog',
                'name' => 'المدونة',
                'description' => 'Blog',
                'icon' => 'assets/icons/stationery/002-book.svg',
                'color' => 'orange',
                'sellable' => false,
            ],
            'store' => [
                'order' => 2,
                'slug' => 'store',
                'model_type' => 'product',
                'name' => 'المتجر',
                'description' => 'Store',
                'icon' => 'assets/icons/ecommerce/018-cart.svg',
                'color' => 'green',
                'sellable' => true,
            ],
        ],
    ]);

    $tabs = collect(app(ContentTypeRegistry::class)->tabs())->keyBy('slug');

    expect($tabs['blog']['sellable'])->toBeFalse()
        ->and($tabs['store']['sellable'])->toBeTrue();
});

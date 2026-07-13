<?php

use App\Support\TablerIconsCatalog;
use Tests\TestCase;

uses(TestCase::class);

it('loads the tabler icons catalog', function () {
    $catalog = app(TablerIconsCatalog::class);

    expect($catalog->all())->not->toBeEmpty()
        ->and($catalog->exists('home'))->toBeTrue()
        ->and($catalog->normalizeId('tabler:home'))->toBe('tabler:home')
        ->and($catalog->normalizeId('home'))->toBe('tabler:home');
});

it('searches and paginates tabler icons', function () {
    $catalog = app(TablerIconsCatalog::class);

    $pageOne = $catalog->search('chart', 1, 10);
    $pageTwo = $catalog->search('chart', 2, 10);

    expect($pageOne['data'])->not->toBeEmpty()
        ->and($pageOne['meta']['page'])->toBe(1)
        ->and($pageOne['meta']['per_page'])->toBe(10)
        ->and($pageOne['meta']['total'])->toBeGreaterThan(0)
        ->and($pageOne['data'][0]['id'])->toStartWith('tabler:')
        ->and($pageTwo['data'])->not->toEqual($pageOne['data']);
});

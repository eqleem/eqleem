<?php

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    view()->prependNamespace('tenant-theme', public_path('themes/default'));
    view()->prependNamespace('default-tenant-theme', public_path('themes/default'));
});

it('shows a loading indicator when a block link is clicked', function () {
    $html = Blade::render(
        '<x-tenant-theme::block-link link="/demo" title="المتجر" icon="hugeicons:store-02" desc="وصف" />'
    );

    expect($html)
        ->toContain('x-on:click="loading = true"')
        ->toContain('x-bind:aria-busy="loading"')
        ->toContain('animate-spin')
        ->toContain('solar:refresh-bold-duotone')
        ->toContain('hugeicons:store-02')
        ->toContain('href="/demo"');
});

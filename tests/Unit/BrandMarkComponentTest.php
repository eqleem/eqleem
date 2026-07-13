<?php

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

uses(TestCase::class);

it('renders icon brand marks with the default icon size', function () {
    $html = Blade::render('<x-brand-mark :mark="$mark" />', [
        'mark' => [
            'type' => 'icon',
            'value' => 'tabler:home',
            'color' => '#dc2626',
            'url' => null,
        ],
    ]);

    expect($html)
        ->toContain('font-size: 2.25rem')
        ->toContain('tabler:home')
        ->toContain('#dc2626');
});

it('renders icon brand marks with a custom icon size', function () {
    $html = Blade::render('<x-brand-mark :mark="$mark" icon-size="4.5rem" />', [
        'mark' => [
            'type' => 'icon',
            'value' => 'tabler:star',
            'color' => '#ffffff',
            'url' => null,
        ],
    ]);

    expect($html)
        ->toContain('font-size: 4.5rem')
        ->toContain('tabler:star');
});

it('applies icon size to emoji brand marks', function () {
    $html = Blade::render('<x-brand-mark :mark="$mark" icon-size="3rem" />', [
        'mark' => [
            'type' => 'emoji',
            'value' => '🏠',
            'color' => '',
            'url' => null,
        ],
    ]);

    expect($html)->toContain('font-size: 3rem');
});

<?php

use App\Support\TenantThemeOptions;
use Tests\TestCase;

uses(TestCase::class);

test('primary palette returns named tailwind palette', function () {
    $options = app(TenantThemeOptions::class);

    $palette = $options->primaryPalette(['primaryColor' => 'teal']);

    expect($palette)
        ->toHaveKeys(['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'])
        ->and($palette['500'])->toContain('oklch');
});

test('primary palette generates shades from a custom hex color', function () {
    $options = app(TenantThemeOptions::class);

    $palette = $options->primaryPalette(['primaryColor' => '#3d5ccc']);

    expect($palette)
        ->toHaveKeys(['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'])
        ->and($palette['500'])->toBe('#3d5ccc')
        ->and($palette['50'])->toStartWith('#')
        ->and($palette['950'])->toStartWith('#')
        ->and($palette['50'])->not->toBe($palette['950']);
});

test('primary palette normalizes short hex colors', function () {
    $options = app(TenantThemeOptions::class);

    $palette = $options->primaryPalette(['primaryColor' => '#f00']);

    expect($palette['500'])->toBe('#ff0000');
});

test('unknown named color falls back to blue palette', function () {
    $options = app(TenantThemeOptions::class);

    $blue = $options->primaryPalette(['primaryColor' => 'blue']);
    $fallback = $options->primaryPalette(['primaryColor' => 'not-a-color']);

    expect($fallback)->toBe($blue);
});

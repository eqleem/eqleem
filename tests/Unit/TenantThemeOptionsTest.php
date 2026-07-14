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

test('secondary palette returns named tailwind palette', function () {
    $options = app(TenantThemeOptions::class);

    $palette = $options->secondaryPalette(['bgColor' => 'teal']);

    expect($palette)
        ->toHaveKeys(['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'])
        ->and($palette['500'])->toContain('oklch');
});

test('secondary palette generates shades from a custom hex color', function () {
    $options = app(TenantThemeOptions::class);

    $palette = $options->secondaryPalette(['bgColor' => '#0d9488']);

    expect($palette)
        ->toHaveKeys(['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'])
        ->and($palette['500'])->toBe('#0d9488')
        ->and($palette['50'])->toStartWith('#')
        ->and($palette['950'])->toStartWith('#')
        ->and($palette['50'])->not->toBe($palette['950']);
});

test('secondary palette resolves shade tokens from background color', function () {
    $options = app(TenantThemeOptions::class);

    $gray = $options->secondaryPalette(['bgColor' => 'gray']);
    $fromShade = $options->secondaryPalette(['bgColor' => 'gray-300']);
    $fromPrefixed = $options->secondaryPalette(['bgColor' => 'bg-gray-200']);

    expect($fromShade)->toBe($gray)
        ->and($fromPrefixed)->toBe($gray);
});

test('unknown secondary named color falls back to gray palette', function () {
    $options = app(TenantThemeOptions::class);

    $gray = $options->secondaryPalette(['bgColor' => 'gray']);
    $fallback = $options->secondaryPalette(['bgColor' => 'not-a-color']);
    $missingPalette = $options->secondaryPalette(['bgColor' => 'stone-200']);

    expect($fallback)->toBe($gray)
        ->and($missingPalette)->toBe($gray);
});

test('secondary palette defaults to gray when option is missing', function () {
    $options = app(TenantThemeOptions::class);

    $default = $options->secondaryPalette([]);
    $gray = $options->secondaryPalette(['bgColor' => 'gray']);

    expect($default)->toBe($gray);
});

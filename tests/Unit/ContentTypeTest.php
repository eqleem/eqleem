<?php

use App\Support\ContentType;

it('resolves tailwind background classes for content type colors', function () {
    expect(ContentType::backgroundClassFor('teal'))->toBe('bg-teal-50')
        ->and(ContentType::backgroundClassFor('blue'))->toBe('bg-blue-50')
        ->and(ContentType::hoverBackgroundClassFor('teal'))->toBe('hover:bg-teal-50')
        ->and(ContentType::backgroundClassFor('unknown'))->toBeNull();
});

it('resolves custom hex colors for content type backgrounds', function () {
    expect(ContentType::backgroundHexFor('#fef3c7'))->toBe('#fef3c7')
        ->and(ContentType::backgroundHexFor('teal'))->toBeNull();
});

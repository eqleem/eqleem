<?php

use Tests\TestCase;

uses(TestCase::class);

it('normalizes sarmady visual size with size-adjust on each font face', function () {
    $sarmadyFaces = collect(config('twind.preflight.@font-face'))
        ->filter(fn (array $face) => ($face['fontFamily'] ?? null) === 'sarmady');

    expect($sarmadyFaces)->toHaveCount(4);

    $sarmadyFaces->each(function (array $face) {
        expect($face)
            ->toHaveKey('sizeAdjust', '173%')
            ->and($face['fontStyle'])->toBe('normal');
    });
});

it('keeps default body font size without font-specific overrides', function () {
    expect(config('twind.preflight.body'))
        ->toBe(['fontSize' => '1rem']);
});

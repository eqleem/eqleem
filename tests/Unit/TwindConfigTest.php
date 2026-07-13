<?php

use Tests\TestCase;

uses(TestCase::class);

it('normalizes sarmady visual size with size-adjust on each font face', function () {
    $sarmadyFaces = collect(config('twind.preflight.@font-face'))
        ->filter(fn (array $face) => ($face['fontFamily'] ?? null) === 'sarmady');

    expect($sarmadyFaces)->toHaveCount(4);

    $sarmadyFaces->each(function (array $face) {
        expect($face)
            ->toHaveKey('sizeAdjust', '160%')
            ->and($face['fontStyle'])->toBe('normal');
    });
});

it('keeps default body font size without font-specific overrides', function () {
    expect(config('twind.preflight.body'))
        ->toBe(['fontSize' => '1rem']);
});

it('limits arabic display fonts to arabic script so digits fall through to ibmps', function () {
    $faces = collect(config('twind.preflight.@font-face'));

    $arabicOnlyFamilies = [
        'sarmady',
        'milligram',
        'eqleem',
        'codec-ultra',
        'vesterbro-poster',
        'wicklow',
        'effra',
    ];

    $expectedRange = 'U+0600-065F, U+066A-06EF, U+06FA-06FF, U+0750-077F, U+08A0-08FF, U+FB50-FDFF, U+FE70-FEFF, U+200C-200F, U+25CC';

    foreach ($arabicOnlyFamilies as $family) {
        $familyFaces = $faces->filter(fn (array $face) => ($face['fontFamily'] ?? null) === $family);

        expect($familyFaces)->not->toBeEmpty();

        $familyFaces->each(function (array $face) use ($expectedRange, $family) {
            expect($face)
                ->toHaveKey('unicodeRange', $expectedRange)
                ->and($face['fontFamily'])->toBe($family);
        });
    }

    $faces
        ->filter(fn (array $face) => ($face['fontFamily'] ?? null) === 'ibmps')
        ->each(fn (array $face) => expect($face)->not->toHaveKey('unicodeRange'));
});

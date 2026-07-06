<?php

it('resolves tabler icons case-insensitively', function () {
    $path = tablerIconPath('search');

    expect(is_file($path))->toBeTrue()
        ->and(strtolower(basename($path)))->toBe('search.svg');
});

it('returns the direct path when the icon filename already matches', function () {
    $path = tablerIconPath('square-rounded-plus');

    expect($path)->toBe(public_path('assets/icons/tabler/square-rounded-plus.svg'))
        ->and(is_file($path))->toBeTrue();
});

<?php

use App\Support\Money;

it('converts major units to minor units', function () {
    expect(Money::toMinor(99))->toBe(9900)
        ->and(Money::toMinor(14.5))->toBe(1450)
        ->and(Money::toMinor(0))->toBe(0)
        ->and(Money::toMinor(null))->toBe(0);
});

it('formats minor units for display', function () {
    expect(Money::format(9900))->toBe('99')
        ->and(Money::format(1450))->toBe('14.50')
        ->and(Money::formatWithCurrency(9900))->toBe('99 '.Money::SAR_SYMBOL)
        ->and(Money::formatWithCurrency(1450))->toBe('14.50 '.Money::SAR_SYMBOL);
});

it('converts minor units back to major units', function () {
    expect(Money::fromMinor(9900))->toBe(99.0)
        ->and(Money::fromMinor(1450))->toBe(14.5);
});

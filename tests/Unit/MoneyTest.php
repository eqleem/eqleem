<?php

use App\Support\Money;
use Illuminate\Support\HtmlString;
use Tests\TestCase;

uses(TestCase::class);

it('converts major units to minor units', function () {
    expect(Money::toMinor(99))->toBe(9900)
        ->and(Money::toMinor(14.5))->toBe(1450)
        ->and(Money::toMinor(0))->toBe(0)
        ->and(Money::toMinor(null))->toBe(0);
});

it('formats minor units for display', function () {
    expect(Money::format(9900))->toBe('99')
        ->and(Money::format(1450))->toBe('14.50')
        ->and(Money::formatWithCurrency(9900))->toBe('99'."\u{00A0}".Money::SAR_SYMBOL)
        ->and(Money::formatWithCurrency(1450))->toBe('14.50'."\u{00A0}".Money::SAR_SYMBOL);
});

it('converts minor units back to major units', function () {
    expect(Money::fromMinor(9900))->toBe(99.0)
        ->and(Money::fromMinor(1450))->toBe(14.5);
});

it('maps currency codes to display symbols', function () {
    expect(Money::symbolFor('SAR'))->toBe(Money::SAR_SYMBOL)
        ->and(Money::symbolFor('USD'))->toBe('$')
        ->and(Money::formatWithCurrency(9900, 'SAR'))->toBe('99'."\u{00A0}".Money::SAR_SYMBOL)
        ->and(Money::formatWithCurrency(9900, 'USD'))->toBe('99'."\u{00A0}".'$');
});

it('renders money with amount before currency in rtl layouts', function () {
    $html = (string) money_format(9900);

    expect($html)
        ->toContain('dir="ltr"')
        ->toContain('class="money-amount">99</span>')
        ->toContain('class="money-symbol">&nbsp;'.Money::SAR_SYMBOL.'</span>');
});

it('returns html string instances for display formatting', function () {
    expect(money_format(9900))->toBeInstanceOf(HtmlString::class);
});

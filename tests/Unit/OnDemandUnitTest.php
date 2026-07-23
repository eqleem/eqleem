<?php

use App\Support\Money;
use App\Support\OnDemandUnit;
use Tests\TestCase;

uses(TestCase::class);

test('on demand unit options include the expected arabic labels', function () {
    expect(OnDemandUnit::options())->toMatchArray([
        OnDemandUnit::SquareMeter => 'متر مربع',
        OnDemandUnit::Foot => 'قدم',
        OnDemandUnit::Piece => 'قطعة',
        OnDemandUnit::Board => 'لوح',
        OnDemandUnit::Roll => 'رول',
        OnDemandUnit::Carton => 'كرتون',
        OnDemandUnit::Kilo => 'كيلو',
        OnDemandUnit::Hour => 'ساعة',
        OnDemandUnit::Other => 'أخرى',
    ]);
});

test('on demand unit label uses custom text for other units', function () {
    expect(OnDemandUnit::label(OnDemandUnit::SquareMeter))->toBe('متر مربع')
        ->and(OnDemandUnit::label(OnDemandUnit::Other, 'متر طولي'))->toBe('متر طولي')
        ->and(OnDemandUnit::label(OnDemandUnit::Other, ''))->toBe('أخرى');
});

test('on demand unit price label formats money with unit', function () {
    expect(OnDemandUnit::priceLabel(2000, OnDemandUnit::SquareMeter))
        ->toBe('20'."\u{00A0}".Money::SAR_SYMBOL.' / متر مربع')
        ->and(OnDemandUnit::priceLabel(1500, OnDemandUnit::Other, 'متر طولي'))
        ->toBe('15'."\u{00A0}".Money::SAR_SYMBOL.' / متر طولي')
        ->and(OnDemandUnit::priceLabel(0, OnDemandUnit::Piece))->toBeNull();
});

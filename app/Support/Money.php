<?php

namespace App\Support;

use Illuminate\Support\Number;

class Money
{
    public const SAR_SYMBOL = "\u{20C1}";

    public static function toMinor(float|string|int|null $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        return (int) round(((float) $amount) * 100);
    }

    public static function fromMinor(int|string|null $minor): float
    {
        return ((int) $minor) / 100;
    }

    public static function format(int|string|null $minor, int $precision = 2): string
    {
        $minorUnits = (int) $minor;
        $fractionDivisor = 10 ** $precision;
        $displayPrecision = ($minorUnits % $fractionDivisor) !== 0 ? $precision : 0;

        return Number::format(self::fromMinor($minorUnits), precision: $displayPrecision);
    }

    public static function formatWithCurrency(int|string|null $minor, ?string $currency = null, int $precision = 2): string
    {
        return self::format($minor, $precision).' '.($currency ?? self::SAR_SYMBOL);
    }
}

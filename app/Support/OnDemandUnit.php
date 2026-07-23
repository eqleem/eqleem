<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class OnDemandUnit
{
    public const SquareMeter = 'square_meter';

    public const Foot = 'foot';

    public const Piece = 'piece';

    public const Board = 'board';

    public const Roll = 'roll';

    public const Carton = 'carton';

    public const Kilo = 'kilo';

    public const Hour = 'hour';

    public const Other = 'other';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::SquareMeter => 'متر مربع',
            self::Foot => 'قدم',
            self::Piece => 'قطعة',
            self::Board => 'لوح',
            self::Roll => 'رول',
            self::Carton => 'كرتون',
            self::Kilo => 'كيلو',
            self::Hour => 'ساعة',
            self::Other => 'أخرى',
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::options());
    }

    public static function rule(): In
    {
        return Rule::in(self::keys());
    }

    public static function label(?string $unitType, ?string $unitLabel = null): string
    {
        if ($unitType === self::Other) {
            $custom = trim((string) $unitLabel);

            return $custom !== '' ? $custom : (self::options()[self::Other] ?? 'أخرى');
        }

        return self::options()[$unitType] ?? '';
    }

    public static function priceLabel(int|string|null $priceMinor, ?string $unitType, ?string $unitLabel = null): ?string
    {
        if ($priceMinor === null || $priceMinor === '' || (int) $priceMinor <= 0) {
            return null;
        }

        $price = money_format_plain((int) $priceMinor);
        $unit = self::label($unitType, $unitLabel);

        if ($unit === '') {
            return $price;
        }

        return $price.' / '.$unit;
    }

    public static function priceHtml(int|string|null $priceMinor, ?string $unitType, ?string $unitLabel = null): ?HtmlString
    {
        if ($priceMinor === null || $priceMinor === '' || (int) $priceMinor <= 0) {
            return null;
        }

        $price = money_format((int) $priceMinor);
        $unit = e(self::label($unitType, $unitLabel));

        if ($unit === '') {
            return $price;
        }

        return new HtmlString($price.' / '.$unit);
    }
}

<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class Money
{
    public const SAR_SYMBOL = "\u{20C1}";

    public const DEFAULT_CURRENCY_CODE = 'SAR';

    /**
     * @return array<string, string>
     */
    public static function currencySymbols(): array
    {
        return [
            'SAR' => self::SAR_SYMBOL,
            'AED' => 'د.إ',
            'KWD' => 'د.ك',
            'BHD' => 'د.ب',
            'QAR' => 'ر.ق',
            'OMR' => 'ر.ع',
            'JOD' => 'د.أ',
            'EGP' => 'ج.م',
            'USD' => '$',
            'EUR' => '€',
        ];
    }

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

    public static function defaultCurrencyCode(): string
    {
        return once(function (): string {
            if (! function_exists('app') || ! app()->bound('config')) {
                return self::DEFAULT_CURRENCY_CODE;
            }

            try {
                return (string) data_get(
                    Setting::localeCurrencySettings(),
                    'default_currency',
                    config('locales.defaults.default_currency', self::DEFAULT_CURRENCY_CODE),
                );
            } catch (\Throwable) {
                return (string) config('locales.defaults.default_currency', self::DEFAULT_CURRENCY_CODE);
            }
        });
    }

    public static function symbolFor(?string $currencyCode = null): string
    {
        $code = strtoupper($currencyCode ?: self::defaultCurrencyCode());
        $symbols = config('locales.currency_symbols', self::currencySymbols());

        return $symbols[$code] ?? $code;
    }

    public static function formatWithCurrency(int|string|null $minor, ?string $currency = null, int $precision = 2): string
    {
        $code = filled($currency) ? strtoupper((string) $currency) : self::defaultCurrencyCode();

        return self::format($minor, $precision)."\u{00A0}".self::symbolFor($code);
    }

    public static function displayWithCurrency(int|string|null $minor, ?string $currency = null, int $precision = 2): HtmlString
    {
        $code = filled($currency) ? strtoupper((string) $currency) : self::defaultCurrencyCode();
        $amount = e(self::format($minor, $precision));
        $symbol = e(self::symbolFor($code));

        return new HtmlString(
            '<span class="money" style="unicode-bidi: isolate; display: inline-block;">'
            .'<span class="money-amount">'.$amount.'</span>'
            .'<span class="money-symbol">&nbsp;'.$symbol.'</span>'
            .'</span>'
        );
    }
}

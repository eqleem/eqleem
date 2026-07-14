<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\File;

class TenantThemeOptions
{
    /** @var array<string, array<string, string>>|null */
    private static ?array $palettes = null;

    /** @var array<string, array<string, mixed>> */
    private array $resolvedCache = [];

    /**
     * Mix ratios for generating Tailwind-like shades from a custom base (~500) hex.
     *
     * @var array<string, array{0: string, 1: float}>
     */
    private const HEX_SHADE_MIXES = [
        '50' => ['#ffffff', 0.95],
        '100' => ['#ffffff', 0.90],
        '200' => ['#ffffff', 0.75],
        '300' => ['#ffffff', 0.60],
        '400' => ['#ffffff', 0.30],
        '500' => ['#ffffff', 0.0],
        '600' => ['#000000', 0.15],
        '700' => ['#000000', 0.30],
        '800' => ['#000000', 0.45],
        '900' => ['#000000', 0.60],
        '950' => ['#000000', 0.75],
    ];

    /**
     * @return array<string, mixed>
     */
    public function resolve(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? currentTenant();

        if (! $tenant) {
            return [];
        }

        $tenant->loadMissing('theme');

        $theme = $tenant->theme;

        if (! $theme) {
            return [];
        }

        $cacheKey = $tenant->id.':'.$theme->id;

        if (array_key_exists($cacheKey, $this->resolvedCache)) {
            return $this->resolvedCache[$cacheKey];
        }

        $schema = $this->schemaForTheme($theme->slug);

        if ($schema === []) {
            return $this->resolvedCache[$cacheKey] = [];
        }

        $saved = $tenant->themeSettingsFor($theme->id);
        $resolved = [];

        foreach ($schema as $key => $field) {
            $resolved[$key] = data_get($saved, $key, $field['default'] ?? null);
        }

        return $this->resolvedCache[$cacheKey] = $resolved;
    }

    /**
     * @return array<int|string, string>
     */
    public function primaryPalette(array $options): array
    {
        return $this->colorPalette(
            (string) ($options['primaryColor'] ?? 'blue'),
            'blue',
        );
    }

    /**
     * @return array<int|string, string>
     */
    public function secondaryPalette(array $options): array
    {
        return $this->colorPalette(
            (string) ($options['bgColor'] ?? 'gray'),
            'gray',
        );
    }

    /**
     * Twind can only apply slash-opacity (e.g. bg-secondary-900/10) to hex colors
     * or values that include an `<alpha-value>` placeholder. Named Tailwind palettes
     * ship as bare `oklch(...)`, so opacity modifiers are silently ignored unless
     * rewritten for Twind.
     *
     * @param  array<int|string, string>  $palette
     * @return array<int|string, string>
     */
    public function forTwind(array $palette): array
    {
        $converted = [];

        foreach ($palette as $shade => $color) {
            $converted[$shade] = $this->toTwindColor((string) $color);
        }

        return $converted;
    }

    /**
     * Rewrite a CSS color so Twind opacity modifiers can inject alpha.
     */
    public function toTwindColor(string $color): string
    {
        $color = trim($color);

        if ($color === '' || str_contains($color, '<alpha-value>')) {
            return $color;
        }

        if ($this->isHexColor($color)) {
            return $this->normalizeHex($color);
        }

        if (preg_match('/^oklch\(\s*([^\/)]+?)\s*\)$/i', $color, $matches) === 1) {
            return 'oklch('.trim($matches[1]).' / <alpha-value>)';
        }

        if (preg_match('/^rgba?\(\s*([\d.]+)\s*[, ]\s*([\d.]+)\s*[, ]\s*([\d.]+)\s*\)$/i', $color, $matches) === 1) {
            return "rgb({$matches[1]} {$matches[2]} {$matches[3]} / <alpha-value>)";
        }

        if (preg_match('/^hsla?\(\s*([\d.]+)\s*[, ]\s*([\d.]+%?)\s*[, ]\s*([\d.]+%?)\s*\)$/i', $color, $matches) === 1) {
            return "hsl({$matches[1]} {$matches[2]} {$matches[3]} / <alpha-value>)";
        }

        return $color;
    }

    /**
     * @return array<int|string, string>
     */
    public function colorPalette(string $color, string $fallback = 'blue'): array
    {
        $palettes = $this->palettes();
        $color = $this->normalizeColorToken($color);

        if (isset($palettes[$color])) {
            return $palettes[$color];
        }

        if ($this->isHexColor($color)) {
            return $this->paletteFromHex($color);
        }

        return $palettes[$fallback] ?? $palettes['blue'] ?? [];
    }

    /**
     * Normalize theme option color tokens such as "gray-300", "bg-stone-200", or "#0d9488".
     */
    public function normalizeColorToken(string $color): string
    {
        $color = trim($color);

        if ($color === '' || in_array($color, ['transparent', 'bg-tranparent', 'white'], true)) {
            return 'gray';
        }

        $color = (string) preg_replace('/^(bg|text)-/', '', $color);

        if (preg_match('/^([a-z]+)-(\d{2,3})$/i', $color, $matches) === 1) {
            return strtolower($matches[1]);
        }

        return $color;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function schemaForTheme(string $themeSlug): array
    {
        $optionsPath = public_path('themes/'.$themeSlug.'/options.json');

        if (! is_file($optionsPath)) {
            return [];
        }

        $schema = json_decode(File::get($optionsPath), true);

        return is_array($schema) ? $schema : [];
    }

    public function isHexColor(string $color): bool
    {
        return (bool) preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color);
    }

    /**
     * Whether a CSS color reads as a light background (dark text recommended).
     */
    public function isLightColor(string $color): bool
    {
        $lightness = $this->perceptualLightness($color);

        if ($lightness === null) {
            return false;
        }

        return $lightness >= 0.62;
    }

    /**
     * Tailwind text class for readable text on the given background color.
     */
    public function contrastTextClass(string $color): string
    {
        return $this->isLightColor($color) ? 'text-stone-900' : 'text-white';
    }

    /**
     * Icon / brand-mark chrome classes that match contrast text on a background.
     */
    public function contrastIconChromeClass(string $color): string
    {
        return $this->isLightColor($color)
            ? 'bg-black/10 text-stone-900'
            : 'bg-white/15 text-white';
    }

    /**
     * @return array<string, string>
     */
    public function paletteFromHex(string $hex): array
    {
        $base = $this->normalizeHex($hex);
        $palette = [];

        foreach (self::HEX_SHADE_MIXES as $shade => [$mixWith, $amount]) {
            $palette[$shade] = $amount <= 0.0
                ? $base
                : $this->mixHex($base, $mixWith, $amount);
        }

        return $palette;
    }

    /**
     * Approximate perceptual lightness in 0–1 from hex or oklch() colors.
     */
    public function perceptualLightness(string $color): ?float
    {
        $color = trim($color);

        if ($color === '' || $color === 'transparent') {
            return null;
        }

        if (preg_match('/^oklch\(\s*([\d.]+)(%?)/i', $color, $matches) === 1) {
            $value = (float) $matches[1];

            return $matches[2] === '%' ? ($value / 100) : $value;
        }

        if (! $this->isHexColor($color)) {
            return null;
        }

        [$r, $g, $b] = $this->hexToRgb($color);

        $channels = array_map(function (int $channel): float {
            $normalized = $channel / 255;

            return $normalized <= 0.03928
                ? $normalized / 12.92
                : (($normalized + 0.055) / 1.055) ** 2.4;
        }, [$r, $g, $b]);

        return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function palettes(): array
    {
        if (self::$palettes !== null) {
            return self::$palettes;
        }

        $path = config_path('tailwind-palettes.json');

        if (! is_file($path)) {
            return self::$palettes = ['blue' => []];
        }

        $decoded = json_decode(File::get($path), true);

        return self::$palettes = is_array($decoded) ? $decoded : ['blue' => []];
    }

    private function normalizeHex(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return '#'.strtolower($hex);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($this->normalizeHex($hex), '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function mixHex(string $from, string $to, float $amount): string
    {
        $amount = max(0.0, min(1.0, $amount));
        [$fr, $fg, $fb] = $this->hexToRgb($from);
        [$tr, $tg, $tb] = $this->hexToRgb($to);

        $r = (int) round($fr + (($tr - $fr) * $amount));
        $g = (int) round($fg + (($tg - $fg) * $amount));
        $b = (int) round($fb + (($tb - $fb) * $amount));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}

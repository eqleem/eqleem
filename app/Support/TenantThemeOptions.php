<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\File;

class TenantThemeOptions
{
    /** @var array<string, array<string, string>>|null */
    private static ?array $palettes = null;

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

        $schema = $this->schemaForTheme($theme->slug);

        if ($schema === []) {
            return [];
        }

        $saved = $tenant->themeSettingsFor($theme->id);
        $resolved = [];

        foreach ($schema as $key => $field) {
            $resolved[$key] = data_get($saved, $key, $field['default'] ?? null);
        }

        return $resolved;
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

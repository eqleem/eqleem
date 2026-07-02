<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\File;

class TenantThemeOptions
{
    /** @var array<string, array<string, string>>|null */
    private static ?array $palettes = null;

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
     * @param  array<string, mixed>  $options
     * @return array<int|string, string>
     */
    public function primaryPalette(array $options): array
    {
        $color = (string) ($options['primaryColor'] ?? 'blue');
        $palettes = $this->palettes();

        return $palettes[$color] ?? $palettes['blue'];
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
}

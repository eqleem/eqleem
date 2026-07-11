<?php

namespace App\API\Page\Concerns;

use App\Models\Theme;
use App\Support\TenantThemeOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

trait MapsPageThemes
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function mappedThemes(?int $tenantThemeId): Collection
    {
        return Theme::query()
            ->where('active', true)
            ->where('public', true)
            ->orderBy('sort')
            ->get(['id', 'name', 'slug', 'meta', 'config', 'type', 'app'])
            ->map(fn (Theme $theme): array => $this->mapTheme($theme, $tenantThemeId));
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapTheme(Theme $theme, ?int $tenantThemeId): array
    {
        $preview = data_get($theme->meta, 'preview', 'assets/wjeez/themes/default.svg');
        $gallery = data_get($theme->meta, 'gallery', [$preview]);
        $price = data_get($theme->meta, 'price');

        return [
            'id' => $theme->id,
            'name' => $theme->name,
            'slug' => $theme->slug,
            'label_ar' => data_get($theme->meta, 'label_ar', $theme->name),
            'image_path' => $theme->image_path,
            'preview_url' => $theme->image_path,
            'gallery' => collect($gallery)->map(fn (string $path): string => asset($path))->all(),
            'type' => $theme->type,
            'app' => $theme->app,
            'designer' => data_get($theme->meta, 'designer', '—'),
            'price_label' => $this->formatThemePrice($price),
            'config' => $theme->config ?? [],
            'is_active' => $theme->id === $tenantThemeId,
        ];
    }

    protected function formatThemePrice(mixed $price): string
    {
        if ($price === null || $price === '' || (is_numeric($price) && (float) $price <= 0)) {
            return 'مجاني';
        }

        return money_format_plain(money_minor((float) $price));
    }

    protected function findPublicTheme(int $themeId): ?Theme
    {
        return Theme::query()
            ->where('id', $themeId)
            ->where('active', true)
            ->where('public', true)
            ->first();
    }

    /**
     * @return array{schema: array<string, array<string, mixed>>, options: array<string, mixed>, previews: array<string, string|null>}
     */
    protected function themeOptionsPayload(Theme $theme, array $saved): array
    {
        $schema = app(TenantThemeOptions::class)->schemaForTheme($theme->slug);
        $options = [];
        $previews = [];

        foreach ($schema as $key => $field) {
            $value = data_get($saved, $key, $field['default'] ?? '');
            $options[$key] = $value;

            if (($field['type'] ?? null) === 'upload-single-image' && filled($value)) {
                $previews[$key] = str_starts_with((string) $value, 'http')
                    ? (string) $value
                    : Storage::url((string) $value);
            }
        }

        return [
            'schema' => $schema,
            'options' => $options,
            'previews' => $previews,
        ];
    }
}

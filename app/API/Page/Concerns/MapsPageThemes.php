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
        $price = data_get($theme->meta, 'price');
        $isFree = $price === null || $price === '' || (is_numeric($price) && (float) $price <= 0);
        $features = data_get($theme->meta, 'features', []);

        return [
            'id' => $theme->id,
            'name' => $theme->name,
            'slug' => $theme->slug,
            'label_ar' => data_get($theme->meta, 'label_ar', $theme->name),
            'description' => data_get($theme->meta, 'description', ''),
            'features' => is_array($features) ? array_values(array_filter($features, fn ($f): bool => filled($f))) : [],
            'version' => data_get($theme->meta, 'version', '1.0.0'),
            'image_path' => $theme->image_path,
            'preview_url' => $theme->image_path,
            'gallery' => $this->resolveThemeGallery($theme),
            'type' => $theme->type,
            'app' => $theme->app,
            'designer' => data_get($theme->meta, 'designer', '—'),
            'price' => $isFree ? 0 : (float) $price,
            'is_free' => $isFree,
            'price_label' => $this->formatThemePrice($price),
            'config' => $theme->config ?? [],
            'is_active' => $theme->id === $tenantThemeId,
        ];
    }

    /**
     * @return list<string>
     */
    protected function resolveThemeGallery(Theme $theme): array
    {
        $preview = data_get($theme->meta, 'preview');
        $gallery = data_get($theme->meta, 'gallery', []);

        if (! is_array($gallery)) {
            $gallery = [];
        }

        $paths = collect([$preview, ...$gallery, $theme->image_path])
            ->filter(fn (mixed $path): bool => filled($path))
            ->map(fn (mixed $path): string => $this->themeAssetUrl((string) $path))
            ->unique()
            ->values()
            ->all();

        return $paths !== [] ? $paths : [$theme->image_path];
    }

    protected function themeAssetUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
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

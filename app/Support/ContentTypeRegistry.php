<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Collection;

class ContentTypeRegistry
{
    /**
     * Active content types from config/content-types.php, ordered for nav/tabs.
     * All types respect the tenant selection after page sections are configured.
     * Older catalog-only preferences continue to affect sellable types only.
     *
     * @return Collection<int, ContentType>
     */
    public function all(?Tenant $tenant = null): Collection
    {
        $tenant = $tenant ?? currentTenant();
        $enabled = data_get($tenant?->config, 'enabled_content_types');
        $hasPrefs = is_array($enabled);
        $managesAllSections = (bool) data_get($tenant?->config, 'page_sections_configured', false);

        return $this->configured()
            ->filter(function (ContentType $contentType) use ($hasPrefs, $enabled, $managesAllSections): bool {
                if ($contentType->permanent) {
                    return true;
                }

                if ($managesAllSections && $hasPrefs) {
                    return in_array($contentType->slug, $enabled, true);
                }

                if (! $contentType->sellable) {
                    return $contentType->active;
                }

                if ($hasPrefs) {
                    return in_array($contentType->slug, $enabled, true);
                }

                return $contentType->active;
            })
            ->values();
    }

    /**
     * Content types controlled by the page section manager.
     *
     * @return Collection<int, ContentType>
     */
    public function managedSections(): Collection
    {
        return $this->configured()
            ->reject(fn (ContentType $contentType): bool => $contentType->permanent)
            ->values();
    }

    /**
     * Every configured content type, including inactive ones.
     *
     * @return Collection<int, ContentType>
     */
    public function configured(): Collection
    {
        return collect(config('content-types', []))
            ->map(fn (array $config, string $slug): ContentType => ContentType::fromConfig($slug, $config))
            ->sortBy('order')
            ->values();
    }

    public function find(string $slug): ?ContentType
    {
        $config = config("content-types.{$slug}");

        if (! is_array($config)) {
            return null;
        }

        return ContentType::fromConfig($slug, $config);
    }

    public function findActive(string $slug): ?ContentType
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tabs(): array
    {
        return $this->all()
            ->map(fn (ContentType $contentType): array => $contentType->toTabArray())
            ->all();
    }
}

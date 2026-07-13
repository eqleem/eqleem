<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Collection;

class ContentTypeRegistry
{
    /**
     * Active content types from config/content-types.php, ordered for nav/tabs.
     * Sellable types respect per-tenant `config.enabled_content_types` when set.
     *
     * @return Collection<int, ContentType>
     */
    public function all(?Tenant $tenant = null): Collection
    {
        $tenant = $tenant ?? currentTenant();
        $enabled = data_get($tenant?->config, 'enabled_content_types');
        $hasPrefs = is_array($enabled);

        return $this->configured()
            ->filter(function (ContentType $contentType) use ($hasPrefs, $enabled): bool {
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

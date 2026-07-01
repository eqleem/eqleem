<?php

namespace App\Support;

use Illuminate\Support\Collection;

class ContentTypeRegistry
{
    /**
     * @return Collection<int, ContentType>
     */
    public function all(): Collection
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

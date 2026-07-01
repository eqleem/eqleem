<?php

namespace App\Support;

use Illuminate\Support\Collection;

class PageTabRegistry
{
    /**
     * @return Collection<int, PageTab>
     */
    public function all(): Collection
    {
        return collect(config('page-tabs', []))
            ->map(fn (array $config, string $slug): PageTab => PageTab::fromConfig($slug, $config))
            ->sortBy('order')
            ->values();
    }

    public function find(string $slug): ?PageTab
    {
        $config = config("page-tabs.{$slug}");

        if (! is_array($config)) {
            return null;
        }

        return PageTab::fromConfig($slug, $config);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tabs(): array
    {
        return $this->all()
            ->map(fn (PageTab $pageTab): array => $pageTab->toTabArray())
            ->all();
    }
}

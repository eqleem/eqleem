<?php

namespace App\Support;

use Illuminate\Support\Collection;

class BlockPositionRegistry
{
    /**
     * @return Collection<int, BlockPosition>
     */
    public function all(): Collection
    {
        return collect(config('block-positions', []))
            ->map(fn (array $config, string $slug): BlockPosition => BlockPosition::fromConfig($slug, $config))
            ->sortBy('order')
            ->values();
    }

    public function find(string $slug): ?BlockPosition
    {
        $config = config("block-positions.{$slug}");

        if (! is_array($config)) {
            return null;
        }

        return BlockPosition::fromConfig($slug, $config);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function options(): array
    {
        return $this->all()
            ->map(fn (BlockPosition $blockPosition): array => $blockPosition->toArray())
            ->all();
    }
}

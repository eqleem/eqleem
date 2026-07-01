<?php

namespace App\Support;

use Illuminate\Support\Collection;

class BlockTypeRegistry
{
    /**
     * @return Collection<int, BlockType>
     */
    public function all(): Collection
    {
        return collect(config('block-types', []))
            ->map(fn (array $config, string $slug): BlockType => BlockType::fromConfig($slug, $config))
            ->sortBy('order')
            ->values();
    }

    public function find(string $slug): ?BlockType
    {
        $config = config("block-types.{$slug}");

        if (! is_array($config)) {
            return null;
        }

        return BlockType::fromConfig($slug, $config);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function options(): array
    {
        return $this->all()
            ->map(fn (BlockType $blockType): array => $blockType->toArray())
            ->all();
    }
}

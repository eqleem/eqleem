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

    /**
     * @return Collection<int, BlockType>
     */
    public function defaults(): Collection
    {
        return $this->all()->filter(fn (BlockType $blockType): bool => $blockType->default);
    }

    /**
     * @return Collection<int, BlockType>
     */
    public function addable(): Collection
    {
        return $this->all()->filter(fn (BlockType $blockType): bool => ! $blockType->default);
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
    public function options(bool $addableOnly = false): array
    {
        $types = $addableOnly ? $this->addable() : $this->all();

        return $types
            ->map(fn (BlockType $blockType): array => $blockType->toArray())
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function iconPaths(): array
    {
        return $this->all()
            ->mapWithKeys(fn (BlockType $blockType): array => [$blockType->slug => $blockType->icon])
            ->all();
    }

    /**
     * @return array<string, ?string>
     */
    public function editors(): array
    {
        return $this->all()
            ->mapWithKeys(fn (BlockType $blockType): array => [$blockType->slug => $blockType->editor])
            ->all();
    }
}

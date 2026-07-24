<?php

namespace App\API\Concerns;

use App\Models\Taxonomy;
use Illuminate\Support\Collection;

/**
 * Shared selectable category options for content editors.
 *
 * Domain traits supply taxonomyCategoryType() (e.g. store_category).
 */
trait ResolvesTaxonomyCategoryOptions
{
    abstract protected function taxonomyCategoryType(): string;

    /** @var Collection<int, array{id: string, label: string, selectable: bool}>|null */
    private ?Collection $cachedCategoryOptions = null;

    /**
     * @return Collection<int, array{id: string, label: string, selectable: bool}>
     */
    protected function categoryOptions(): Collection
    {
        if ($this->cachedCategoryOptions instanceof Collection) {
            return $this->cachedCategoryOptions;
        }

        $tree = Taxonomy::flatTree($this->taxonomyCategoryType());
        $parentIds = $tree
            ->pluck('parent_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->flip();

        return $this->cachedCategoryOptions = $tree
            ->map(fn (Taxonomy $item): array => [
                'id' => (string) $item->id,
                'label' => str_repeat('— ', (int) ($item->depth ?? 0)).$item->name,
                'selectable' => ! $parentIds->has((int) $item->id),
            ]);
    }

    /**
     * @param  array<int, int|string>  $categoryIds
     * @return list<int>
     */
    protected function selectableCategoryIds(array $categoryIds): array
    {
        $selectableIds = $this->categoryOptions()
            ->where('selectable', true)
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        return collect($categoryIds)
            ->map(fn (mixed $id): string => (string) $id)
            ->intersect($selectableIds)
            ->map(fn (string $id): int => (int) $id)
            ->values()
            ->all();
    }
}

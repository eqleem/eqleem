<?php

namespace App\API\Concerns;

use App\Models\Taxonomy;
use Illuminate\Support\Collection;

/**
 * Shared category-tree mapping for dashboard taxonomy CRUD.
 *
 * Domain traits supply taxonomyCategoryType() (e.g. store_category).
 */
trait MapsTaxonomyCategories
{
    abstract protected function taxonomyCategoryType(): string;

    /** @var Collection<int, Taxonomy>|null */
    private ?Collection $categoryTreeCache = null;

    /**
     * @return Collection<int, Taxonomy>
     */
    protected function categoryTree(): Collection
    {
        return $this->categoryTreeCache ??= Taxonomy::flatTree($this->taxonomyCategoryType());
    }

    /**
     * @return Collection<int, array{id: int, name: string, slug: string, description: string|null, parent_id: int|null, depth: int, sort_order: int}>
     */
    protected function mapCategoryTree(?string $search = null): Collection
    {
        $categories = $this->categoryTree();

        if ($search !== null && $search !== '') {
            $term = mb_strtolower($search);

            $categories = $categories->filter(function (Taxonomy $category) use ($term): bool {
                return str_contains(mb_strtolower($category->name), $term)
                    || str_contains(mb_strtolower((string) $category->description), $term);
            })->values();
        }

        return $categories->map(fn (Taxonomy $category): array => $this->mapCategory($category));
    }

    /**
     * @return array{id: int, name: string, slug: string, description: string|null, parent_id: int|null, depth: int, sort_order: int}
     */
    protected function mapCategory(Taxonomy $category): array
    {
        return [
            'id' => (int) $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'parent_id' => $category->parent_id !== null ? (int) $category->parent_id : null,
            'depth' => (int) ($category->depth ?? 0),
            'sort_order' => (int) ($category->sort_order ?? 0),
        ];
    }

    /**
     * @param  list<int>  $excludeIds
     * @return list<array{id: string, label: string}>
     */
    protected function parentCategoryOptions(array $excludeIds = []): array
    {
        $excluded = collect($excludeIds)->map(fn (mixed $id): int => (int) $id)->all();

        $options = [
            ['id' => '', 'label' => 'بدون تصنيف أب'],
        ];

        foreach ($this->categoryTree() as $item) {
            if (in_array((int) $item->id, $excluded, true)) {
                continue;
            }

            $options[] = [
                'id' => (string) $item->id,
                'label' => str_repeat('— ', (int) ($item->depth ?? 0)).$item->name,
            ];
        }

        return $options;
    }

    /**
     * Nested-set descendants (includes self). Uses dedicated queries so the
     * request-scoped category tree cache is not populated before mutations.
     *
     * @return list<int>
     */
    protected function descendantCategoryIds(int $categoryId): array
    {
        $category = Taxonomy::query()
            ->type($this->taxonomyCategoryType())
            ->whereKey($categoryId)
            ->first(['id', 'lft', 'rgt']);

        if (! $category instanceof Taxonomy) {
            return [$categoryId];
        }

        return Taxonomy::query()
            ->type($this->taxonomyCategoryType())
            ->where('lft', '>=', (int) $category->lft)
            ->where('rgt', '<=', (int) $category->rgt)
            ->orderBy('lft')
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }
}

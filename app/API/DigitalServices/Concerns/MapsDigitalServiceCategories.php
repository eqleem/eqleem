<?php

namespace App\API\DigitalServices\Concerns;

use App\Models\Taxonomy;
use Illuminate\Support\Collection;

trait MapsDigitalServiceCategories
{
    /** @var Collection<int, Taxonomy>|null */
    private ?Collection $categoryTreeCache = null;

    /**
     * @return Collection<int, Taxonomy>
     */
    protected function categoryTree(): Collection
    {
        return $this->categoryTreeCache ??= Taxonomy::flatTree('digital_service_category');
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
     * @return list<int>
     */
    protected function descendantCategoryIds(int $categoryId): array
    {
        $ids = [$categoryId];
        $remaining = [$categoryId];

        while ($remaining !== []) {
            $children = Taxonomy::query()
                ->type('digital_service_category')
                ->whereIn('parent_id', $remaining)
                ->pluck('id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();

            $remaining = array_values(array_diff($children, $ids));
            $ids = array_values(array_unique([...$ids, ...$children]));
        }

        return $ids;
    }
}

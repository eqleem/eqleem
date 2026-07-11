<?php

namespace App\API\Store\Concerns;

use App\Models\Content;
use App\Models\Taxonomy;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesStoreProduct
{
    protected function storeType(): string
    {
        return contentTypeModel('store');
    }

    protected function findStoreProduct(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->storeType())
            ->where('uuid', $uuid)
            ->first();

        if (! $content instanceof Content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    /**
     * @return Collection<int, array{id: string, label: string, selectable: bool}>
     */
    protected function categoryOptions(): Collection
    {
        $parentIds = Taxonomy::query()
            ->type('store_category')
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->flip();

        return Taxonomy::flatTree('store_category')
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

    protected function uniqueStoreSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'product';
        $counter = 1;

        while (
            Content::query()
                ->where('slug', $slug)
                ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugifyTitle(string $title): string
    {
        $slug = Str::slug($title);

        return $slug !== '' ? $slug : 'product';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/store/product/';
    }
}

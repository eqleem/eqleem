<?php

namespace App\API\UnitRental\Concerns;

use App\Models\Calendar;
use App\Models\Content;
use App\Models\Taxonomy;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesUnitRental
{
    protected function unitRentalType(): string
    {
        return contentTypeModel('unit-rental');
    }

    protected function findUnitRental(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->unitRentalType())
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
            ->type('unit_category')
            ->whereNotNull('parent_id')
            ->pluck('parent_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->flip();

        return Taxonomy::flatTree('unit_category')
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

    protected function uniqueUnitRentalSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'unit';
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

        return $slug !== '' ? $slug : 'unit';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/units/';
    }

    /**
     * @return Collection<int, array{id: string, label: string, selectable: bool}>
     */
    protected function calendarOptions(): Collection
    {
        return Calendar::query()
            ->where('type', 'rental-unit')
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Calendar $calendar): array => [
                'id' => (string) $calendar->id,
                'label' => $calendar->name,
                'selectable' => true,
            ]);
    }

    /**
     * @param  array<int, int|string>  $calendarIds
     * @return list<int>
     */
    protected function selectableCalendarIds(array $calendarIds): array
    {
        $selectableIds = $this->calendarOptions()
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        return collect($calendarIds)
            ->map(fn (mixed $id): string => (string) $id)
            ->intersect($selectableIds)
            ->map(fn (string $id): int => (int) $id)
            ->values()
            ->all();
    }
}

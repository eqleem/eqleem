<?php

namespace App\API\Services\Concerns;

use App\API\Concerns\ResolvesTaxonomyCategoryOptions;
use App\Models\Calendar;
use App\Models\Content;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesService
{
    use ResolvesTaxonomyCategoryOptions;

    protected function taxonomyCategoryType(): string
    {
        return Content::TAXONOMY_SERVICE;
    }

    protected function serviceType(): string
    {
        return contentTypeModel('services');
    }

    protected function findService(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->serviceType())
            ->where('uuid', $uuid)
            ->first();

        if (! $content instanceof Content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    protected function uniqueServiceSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'service';
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

        return $slug !== '' ? $slug : 'service';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/services/';
    }

    /**
     * @return Collection<int, array{id: string, label: string, selectable: bool}>
     */
    protected function calendarOptions(): Collection
    {
        return Calendar::query()
            ->select(['id', 'name'])
            ->where('type', 'service-provider')
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

<?php

namespace App\API\DigitalServices\Concerns;

use App\API\Concerns\ResolvesTaxonomyCategoryOptions;
use App\Models\Content;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesDigitalService
{
    use ResolvesTaxonomyCategoryOptions;

    protected function taxonomyCategoryType(): string
    {
        return 'digital_service_category';
    }

    protected function digitalServiceType(): string
    {
        return contentTypeModel('digital-services');
    }

    protected function findDigitalService(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->digitalServiceType())
            ->where('uuid', $uuid)
            ->first();

        if (! $content instanceof Content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    protected function uniqueDigitalServiceSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'digital-service';
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

        return $slug !== '' ? $slug : 'digital-service';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/digital-services/';
    }
}

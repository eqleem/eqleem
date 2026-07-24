<?php

namespace App\API\DigitalProducts\Concerns;

use App\API\Concerns\ResolvesTaxonomyCategoryOptions;
use App\Models\Content;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesDigitalProduct
{
    use ResolvesTaxonomyCategoryOptions;

    protected function taxonomyCategoryType(): string
    {
        return 'digital_store_category';
    }

    protected function digitalProductType(): string
    {
        return contentTypeModel('digital-products');
    }

    protected function findDigitalProduct(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->digitalProductType())
            ->where('uuid', $uuid)
            ->first();

        if (! $content instanceof Content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    protected function uniqueDigitalProductSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'digital-product';
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

        return $slug !== '' ? $slug : 'digital-product';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/digital-products/';
    }
}

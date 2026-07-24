<?php

namespace App\API\Menu\Concerns;

use App\API\Concerns\ResolvesTaxonomyCategoryOptions;
use App\Models\Content;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesMenuItem
{
    use ResolvesTaxonomyCategoryOptions;

    protected function taxonomyCategoryType(): string
    {
        return 'menu_category';
    }

    protected function menuType(): string
    {
        return contentTypeModel('menu');
    }

    protected function findMenuItem(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->menuType())
            ->where('uuid', $uuid)
            ->first();

        if (! $content instanceof Content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    protected function uniqueMenuSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'menu-item';
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

        return $slug !== '' ? $slug : 'menu-item';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/menu/item/';
    }
}

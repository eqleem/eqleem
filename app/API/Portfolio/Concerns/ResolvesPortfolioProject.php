<?php

namespace App\API\Portfolio\Concerns;

use App\API\Concerns\ResolvesTaxonomyCategoryOptions;
use App\Models\Content;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesPortfolioProject
{
    use ResolvesTaxonomyCategoryOptions;

    protected function taxonomyCategoryType(): string
    {
        return 'portfolio_category';
    }

    protected function portfolioType(): string
    {
        return contentTypeModel('portfolio');
    }

    protected function findPortfolioProject(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->portfolioType())
            ->where('uuid', $uuid)
            ->first();

        if (! $content instanceof Content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    protected function uniquePortfolioSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'project';
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

        return $slug !== '' ? $slug : 'project';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/portfolio/';
    }
}

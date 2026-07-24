<?php

namespace App\API\Blog\Concerns;

use App\API\Concerns\ResolvesTaxonomyCategoryOptions;
use App\Models\Content;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesBlogPost
{
    use ResolvesTaxonomyCategoryOptions;

    protected function taxonomyCategoryType(): string
    {
        return 'blog_category';
    }

    protected function blogType(): string
    {
        return contentTypeModel('blog');
    }

    protected function findBlogPost(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->blogType())
            ->where('uuid', $uuid)
            ->first();

        if (! $content instanceof Content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    protected function uniqueBlogSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'post';
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

        return $slug !== '' ? $slug : 'post';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/blog/';
    }
}

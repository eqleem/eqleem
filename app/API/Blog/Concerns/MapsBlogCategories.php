<?php

namespace App\API\Blog\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;

trait MapsBlogCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return 'blog_category';
    }
}

<?php

namespace App\API\Blog\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;
use App\Models\Content;

trait MapsBlogCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return Content::TAXONOMY_BLOG;
    }
}

<?php

namespace App\API\Portfolio\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;
use App\Models\Content;

trait MapsPortfolioCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return Content::TAXONOMY_PORTFOLIO;
    }
}

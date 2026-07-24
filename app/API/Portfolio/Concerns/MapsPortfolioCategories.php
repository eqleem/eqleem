<?php

namespace App\API\Portfolio\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;

trait MapsPortfolioCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return 'portfolio_category';
    }
}

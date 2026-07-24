<?php

namespace App\API\Store\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;

trait MapsStoreCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return 'store_category';
    }
}

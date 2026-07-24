<?php

namespace App\API\Services\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;

trait MapsServiceCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return 'service_category';
    }
}

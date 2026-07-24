<?php

namespace App\API\DigitalProducts\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;

trait MapsDigitalProductCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return 'digital_store_category';
    }
}

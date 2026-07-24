<?php

namespace App\API\DigitalServices\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;

trait MapsDigitalServiceCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return 'digital_service_category';
    }
}

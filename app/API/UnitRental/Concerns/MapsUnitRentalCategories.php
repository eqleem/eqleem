<?php

namespace App\API\UnitRental\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;

trait MapsUnitRentalCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return 'unit_category';
    }
}

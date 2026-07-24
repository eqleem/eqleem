<?php

namespace App\API\UnitRental\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;
use App\Models\Content;

trait MapsUnitRentalCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return Content::TAXONOMY_UNIT;
    }
}

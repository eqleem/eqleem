<?php

namespace App\API\DigitalServices\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;
use App\Models\Content;

trait MapsDigitalServiceCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return Content::TAXONOMY_DIGITAL_SERVICE;
    }
}

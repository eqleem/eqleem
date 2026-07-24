<?php

namespace App\API\DigitalProducts\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;
use App\Models\Content;

trait MapsDigitalProductCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return Content::TAXONOMY_DIGITAL_STORE;
    }
}

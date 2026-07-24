<?php

namespace App\API\Menu\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;

trait MapsMenuCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return 'menu_category';
    }
}

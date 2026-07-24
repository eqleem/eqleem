<?php

namespace App\API\Courses\Concerns;

use App\API\Concerns\MapsTaxonomyCategories;

trait MapsCourseCategories
{
    use MapsTaxonomyCategories;

    protected function taxonomyCategoryType(): string
    {
        return 'course_category';
    }
}

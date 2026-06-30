<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Tenantable extends MorphPivot
{
    public $incrementing = false;

    protected $table = 'tenantables';

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'meta' => 'array',
        ];
    }
}

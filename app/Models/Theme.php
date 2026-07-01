<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
  
#[SoftDeletes]
class Theme extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'config' => 'array',
            'active' => 'boolean',
            'public' => 'boolean',
            'sort' => 'integer',
        ];
    }
}

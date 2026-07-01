<?php

namespace App\Models;

use Aliziodev\LaravelTaxonomy\Models\Taxonomy as BaseTaxonomy;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;

class Taxonomy extends BaseTaxonomy
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'type',
        'description',
        'parent_id',
        'sort_order',
        'lft',
        'rgt',
        'depth',
        'meta',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            if ($tenantId = currentTenantId()) {
                $builder->where('tenant_id', $tenantId);
            }
        });
    }
}

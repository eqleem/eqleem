<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Models\Tenantable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait MorphTenantable
{
    public function tenants(): MorphToMany
    {
        return $this->morphToMany(Tenant::class, 'tenantable', 'tenantables', 'tenantable_id', 'tenant_id')
            ->using(Tenantable::class)
            ->withPivot(['active', 'meta'])
            ->withTimestamps();
    }

    public static function boot()
    {
        parent::boot();

        $tenant = tenant();

        if ($tenant) {

            // get
            static::addGlobalScope('tenantable', function (Builder $builder) use ($tenant) {
                $builder->whereHas('tenants', function (Builder $builder) use ($tenant) {
                    $key = $tenant instanceof Model ? $tenant->getKeyName() : (is_int($tenant) ? 'id' : 'handle');
                    $value = $tenant instanceof Model ? $tenant->{$key} : $tenant;
                    $builder->where($key, $value);
                });

                // save
                static::saved(function (self $model) use ($tenant) {
                    $model->attachTenants($tenant);
                });
            });
        }

        // #TODO , not working yet, doesnt deatach, maybe wrong event, not in booted or something,
        static::deleted(function (self $model) {
            $model->tenants()->detach();
        });
    }

    public function attachTenants($tenants)
    {
        return $this->syncTenants($tenants, false);
    }

    public function syncTenants($tenants, bool $detaching = true)
    {
        $this->tenants()->sync($tenants, $detaching);

        return $this;
    }
}

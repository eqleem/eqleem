<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function bootBelongsToTenant()
    {
        // save
        static::creating(function (self $model) {
            $tenantId = currentTenantId();

            if ($tenantId && is_null($model->tenant_id)) {
                $model->tenant_id = $tenantId;
            }
        });
    }
}

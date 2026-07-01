<?php

namespace App\Http\Middleware\Concerns;

use App\Models\Tenant;

trait BindsResolvedTenant
{
    protected function bindResolvedTenant(Tenant $tenant): void
    {
        setCurrentTenant($tenant);
    }

    protected function abortWhenTenantMissing(?Tenant $tenant): void
    {
        if ($tenant === null) {
            abort(404);
        }
    }
}

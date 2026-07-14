<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\BindsResolvedTenant;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromPath
{
    use BindsResolvedTenant;

    /**
     * Path-based tenant resolution strategy.
     *
     * Additional strategies (e.g. subdomain, custom domain) can be added
     * as separate middleware without changing application logic.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $handle = $request->route('tenant');

        $tenant = Tenant::query()
            ->with('theme')
            ->where('handle', $handle)
            ->where('active', true)
            ->first();

        $this->abortWhenTenantMissing($tenant);
        $this->bindResolvedTenant($tenant);

        // set current tenant
        setCurrentTenant($tenant);

        return $next($request);
    }
}

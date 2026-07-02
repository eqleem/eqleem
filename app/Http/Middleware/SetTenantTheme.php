<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantTheme
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        view()->prependNamespace('tenant-theme', public_path('themes/' . tenant('theme.slug', 'default')));
        view()->prependNamespace('default-tenant-theme', public_path('themes/default'));
        
        return $next($request);
    }
}

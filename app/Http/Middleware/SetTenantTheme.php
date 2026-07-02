<?php

namespace App\Http\Middleware;

use App\Support\TenantThemeOptions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class SetTenantTheme
{
    public function __construct(public TenantThemeOptions $themeOptions) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = currentTenant();
        $themeSlug = 'default';

        if ($tenant) {
            $tenant->loadMissing('theme');
            $themeSlug = $tenant->theme?->slug ?? 'default';
        }

        view()->prependNamespace('tenant-theme', public_path('themes/'.$themeSlug));
        view()->prependNamespace('default-tenant-theme', public_path('themes/default'));

        $options = $this->themeOptions->resolve($tenant);
        $primaryPalette = $this->themeOptions->primaryPalette($options);

        view()->share('themeOptions', $options);
        view()->share('themePrimaryPalette', $primaryPalette);

        Context::add('theme_options', $options);
        Context::add('theme_primary_palette', $primaryPalette);

        return $next($request);
    }
}

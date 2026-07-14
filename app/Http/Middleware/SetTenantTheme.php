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
        $primaryPalette = $this->themeOptions->forTwind($this->themeOptions->primaryPalette($options));
        $secondaryPalette = $this->themeOptions->forTwind($this->themeOptions->secondaryPalette($options));

        view()->share('themeOptions', $options);
        view()->share('themePrimaryPalette', $primaryPalette);
        view()->share('themeSecondaryPalette', $secondaryPalette);

        Context::add('theme_options', $options);
        Context::add('theme_primary_palette', $primaryPalette);
        Context::add('theme_secondary_palette', $secondaryPalette);

        return $next($request);
    }
}

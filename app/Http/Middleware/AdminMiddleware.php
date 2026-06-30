<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // dd(auth()->user());
        // dd(auth()->user()->tenant);
        $tenant = auth()->user()?->currentTenant ?? auth()->user()?->tenant;

        if ($tenant) {
            setCurrentTenant($tenant);
        }

        return $next($request);
    }
}

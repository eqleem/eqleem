<?php

use App\Actions\HandleClientSocialCallback;
use App\Http\Middleware\ResolveTenantFromPath;
use App\Http\Middleware\SetTenantTheme;
use App\Models\Tenant;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::middleware('web')->group(function () {
    Route::get('/client/auth/{provider}/callback', function (string $provider) {
        if (! in_array($provider, ['google', 'github'], true)) {
            return redirect()->route('home');
        }

        $tenantId = session('client_auth_tenant_id');

        if (! $tenantId) {
            return redirect()->route('home');
        }

        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            return redirect()->route('home');
        }

        try {
            $socialUser = Socialite::driver($provider)
                ->redirectUrl(route('client.auth.callback', ['provider' => $provider]))
                ->user();

            HandleClientSocialCallback::run($provider, $socialUser, $tenant);
        } catch (Throwable $exception) {
            report($exception);
            session()->flash('client_auth_error', 'تعذر إكمال تسجيل الدخول. يرجى المحاولة مرة أخرى.');
        }

        session()->forget('client_auth_tenant_id');

        return redirect()->route('tenant.home', ['tenant' => $tenant->handle]);
    })->name('client.auth.callback');
});

Route::prefix('{tenant}')
    ->middleware([ResolveTenantFromPath::class, SetTenantTheme::class])
    ->as('tenant.')
    ->group(function () {
        Route::get('/client/auth/{provider}', function (string $provider) {
            if (! in_array($provider, ['google', 'github'], true)) {
                return redirect()->route('tenant.home', ['tenant' => tenant('handle')]);
            }

            session(['client_auth_tenant_id' => tenant('id')]);

            app(CartService::class)->stashGuestCartReference((int) tenant('id'));

            return Socialite::driver($provider)
                ->redirectUrl(route('client.auth.callback', ['provider' => $provider]))
                ->redirect();
        })->name('client.auth.social');

        Route::post('/client/logout', function (Request $request) {
            Auth::guard('client')->logout();

            return redirect()->route('tenant.home', ['tenant' => tenant('handle')]);
        })->name('client.logout');
    });

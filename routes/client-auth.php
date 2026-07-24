<?php

use App\Actions\VerifyClientLoginCode;
use App\Http\Middleware\ResolveTenantFromPath;
use App\Http\Middleware\SetTenantTheme;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

Route::prefix('{tenant}')
    ->middleware([ResolveTenantFromPath::class, SetTenantTheme::class])
    ->as('tenant.')
    ->group(function () {
        Route::get('/client/auth/email', function (Request $request) {
            $tenant = currentTenant();

            abort_unless($tenant, 404);

            $email = (string) $request->query('email', '');
            $code = (string) $request->query('code', '');

            try {
                VerifyClientLoginCode::run($email, $code, (int) $tenant->id);
            } catch (ValidationException $exception) {
                session()->flash(
                    'client_auth_error',
                    $exception->errors()['code'][0]
                        ?? $exception->errors()['email'][0]
                        ?? 'رابط الدخول غير صالح أو منتهٍ. يرجى طلب رابط جديد.'
                );

                return redirect()->route('tenant.home', ['tenant' => $tenant->handle]);
            }

            return redirect()->to(clientAuthIntendedUrl($tenant));
        })->middleware('signed')->name('client.auth.email');

        Route::get('/client/auth/{provider}', function (string $provider) {
            if ($provider !== 'google') {
                return redirect()->route('tenant.home', ['tenant' => tenant('handle')]);
            }

            session(['client_auth_tenant_id' => tenant('id')]);

            app(CartService::class)->stashGuestCartReference((int) tenant('id'));

            // Same Google redirect URI as web-user OAuth (services.google.redirect)
            // so one Google Cloud authorized URI works for both guards.
            return Socialite::driver($provider)->redirect();
        })->name('client.auth.social')->whereIn('provider', ['google']);

        Route::post('/client/logout', function (Request $request) {
            Auth::guard('client')->logout();

            return redirect()->route('tenant.home', ['tenant' => tenant('handle')]);
        })->name('client.logout');
    });

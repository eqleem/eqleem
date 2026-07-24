<?php

use App\Actions\HandleClientSocialCallback;
use App\Actions\HandleSocialCallback;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::livewire('/reset-password/{token}', 'auth.password-reset')->middleware('guest')->name('password.reset')->middleware('guest');

// auth routes
Route::as('auth.')
    ->middleware(['web'])
    ->group(function () {
        Route::livewire('/register/verify/{token}', 'auth::register-verify')->middleware('guest')->name('register.verify');

        Route::livewire('/password/forgot-password', 'auth::forgot-password')->middleware('guest')->name('password.forgot-password')->middleware('guest');
        Route::livewire('/register-login', 'auth::register-login')->name('register-login')->middleware('guest');
        Route::livewire('/login', 'auth::register-login')->name('login')->middleware('guest');
        Route::livewire('/register', 'auth::register-login')->name('register')->middleware('guest');

        Route::get('/logout', function (Request $request) {
            auth()->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            // app()->instance('tenant', null);
            config()->set('tenant', null);

            return redirect()->route('home');
        })->name('logout')->middleware('auth');
    });

Route::get('/auth/{social}', function ($social) {
    if (! in_array($social, ['github', 'facebook', 'google'])) {
        return redirect()->route('home');
    }

    // User (web guard) OAuth must not be treated as client login.
    session()->forget('client_auth_tenant_id');

    return Socialite::driver($social)->redirect();
})->name('auth.social');

Route::get('/auth/{social}/callback', function ($social) {
    if (! in_array($social, ['github', 'facebook', 'google'])) {
        return redirect()->route('auth.register-login');
    }

    // Client Google login reuses this same Google redirect URI so it does not
    // conflict with the user (web) guard OAuth app configuration.
    if ($social === 'google' && session()->has('client_auth_tenant_id')) {
        $tenantId = (int) session('client_auth_tenant_id');
        $tenant = Tenant::query()->find($tenantId);

        session()->forget('client_auth_tenant_id');

        if (! $tenant) {
            return redirect()->route('home');
        }

        try {
            $socialUser = Socialite::driver($social)->user();

            HandleClientSocialCallback::run($social, $socialUser, $tenant);
        } catch (Throwable $exception) {
            report($exception);
            session()->flash('client_auth_error', 'تعذر إكمال تسجيل الدخول. يرجى المحاولة مرة أخرى.');

            return redirect()->route('tenant.home', ['tenant' => $tenant->handle]);
        }

        return redirect()->to(clientAuthIntendedUrl($tenant));
    }

    $socialUser = Socialite::driver($social)->user();
    $user = HandleSocialCallback::run($social, $socialUser);

    auth()->login($user);

    return redirect(route('dashboard'));
})->name('auth.social.callback');

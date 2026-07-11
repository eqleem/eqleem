<?php

use App\Actions\HandleSocialCallback;
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
        Route::livewire('/login', 'auth::login')->name('login')->middleware('guest');
        Route::livewire('/register', 'auth::register')->name('register')->middleware('guest');

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

    return Socialite::driver($social)->redirect();
})->name('auth.social');

Route::get('/auth/{social}/callback', function ($social) {

    if (! in_array($social, ['github', 'facebook', 'google'])) {
        return redirect()->route('auth.register-login');
    }

    $socialUser = Socialite::driver($social)->user();
    $user = HandleSocialCallback::run($social, $socialUser);

    auth()->login($user);

    return redirect(route('dashboard'));
});

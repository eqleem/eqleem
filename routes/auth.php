<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
  
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\SocialAccount;
use App\Actions\CreateTenant;
use App\Models\Tenant;

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
    if(!in_array($social, ['github', 'facebook', 'google'])) {
        return redirect()->route('home');
    }
    
    return Socialite::driver($social)->redirect();
})->name('auth.social');
 

Route::get('/auth/{social}/callback', function ($social) {

    if(!in_array($social, ['github', 'facebook', 'google'])) {
        return redirect()->route('auth.register-login');
    }

    $user = Socialite::driver($social)->user();
   
    $checkEmail = User::where('email', $user->getEmail())->first();

    if($checkEmail) {
        // create social account
        $socialAccount = SocialAccount::firstOrCreate([
            'user_id' => $checkEmail->id,
            'provider' => $social
        ],[
            'provider_id' => $user->getId(),
            'provider_token' => data_get($user, 'token'),
            'provider_refresh_token' => data_get($user, 'refreshToken'),
            'meta' => $user,
        ]);

        auth()->login($checkEmail);
       
        return redirect(route('admin.home'));
    }

    $myUser = User::firstOrCreate(
        ['email' => $user->getEmail()], 
        [
            'name' => $user->getName(),
            'image' => $user->getAvatar(),
            'username' => $user->getNickname().'-'.rand(1000,9999),
        ]);
  
        // create social account
        $socialAccount = SocialAccount::firstOrCreate([
            'user_id' => $myUser->id,
            'provider' => $social], 
        [
            'provider_id' => $user->getId(),
            'provider_token' => data_get($user, 'token'),
            'provider_refresh_token' => data_get($user, 'refreshToken'),
            'meta' => $user,
        ]);


    // create tenant for the user if not exists    
    $tenant = Tenant::where('user_id', $myUser->id)->first();
    if(!$tenant) {
        $tenant = CreateTenant::run([
            'tenant_name' => $user->name,
            'tenant_handle' => generateKey(7),
            'email' => $user->getEmail(),
            'user_id' => $myUser->id,
        ]);
    }

    $myUser->update([
        'current_tenant_id' => $tenant->id,
    ]);
 
    auth()->login($myUser);

    return redirect(route('admin.home'));
});
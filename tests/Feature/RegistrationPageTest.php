<?php

it('only offers google and email registration methods', function () {
    $this->get(route('auth.register'))
        ->assertSuccessful()
        ->assertSee(route('auth.social', ['social' => 'google']), false)
        ->assertSee('البريد الإلكتروني')
        ->assertDontSee(route('auth.social', ['social' => 'github']), false)
        ->assertDontSee('التسجيل بكلمة المرور')
        ->assertDontSee('كلمة المرور');
});

it('uses the combined authentication route across the home page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('href="'.route('auth.register-login').'"', false)
        ->assertDontSee('href="'.route('auth.register').'"', false)
        ->assertDontSee('href="'.route('auth.login').'"', false);
});

it('hides the login and registration tabs from the authentication layout', function () {
    $this->get(route('auth.register-login'))
        ->assertSuccessful()
        ->assertDontSee('href="'.route('auth.register').'"', false)
        ->assertDontSee('href="'.route('auth.login').'"', false);
});

it('only offers google and email on the register-login page', function () {
    $this->get(route('auth.register-login'))
        ->assertSuccessful()
        ->assertSee(route('auth.social', ['social' => 'google']), false)
        ->assertSee('bg-red-700')
        ->assertDontSee(route('auth.social', ['social' => 'github']), false);
});

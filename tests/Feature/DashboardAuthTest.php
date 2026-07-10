<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login from the dashboard', function () {
    $this->get('/dashboard')
        ->assertRedirect(route('auth.login'));
});

test('guests are redirected to login from dashboard sub-paths', function () {
    $this->get('/dashboard/orders')
        ->assertRedirect(route('auth.login'));
});

test('authenticated users can view the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

test('authenticated users can view dashboard sub-paths', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard/orders')
        ->assertOk();
});

test('guests receive 401 from the dashboard context api', function () {
    $this->getJson('/api/dashboard/context')
        ->assertUnauthorized();
});

test('session-authenticated users can fetch dashboard context from the api', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/dashboard/context')
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id);
});

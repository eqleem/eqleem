<?php

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
});

it('creates a tenant when registering via github', function () {
    Http::fake([
        '*' => Http::response(['data' => ['id' => 'traffic-123']], 201),
    ]);

    Socialite::fake('github', (new SocialiteUser)->map([
        'id' => 'github-1',
        'name' => null,
        'nickname' => 'octocat',
        'email' => 'octocat@example.com',
        'avatar' => 'https://github.com/avatar.png',
    ]));

    $this->get('/auth/github/callback')->assertRedirect(route('admin.home'));

    $user = User::where('email', 'octocat@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->current_tenant_id)->not->toBeNull()
        ->and(Tenant::where('user_id', $user->id)->exists())->toBeTrue();
});

it('creates a tenant for an existing user without one', function () {
    Http::fake([
        '*' => Http::response(['data' => ['id' => 'traffic-456']], 201),
    ]);

    $user = User::factory()->create([
        'email' => 'existing@example.com',
        'current_tenant_id' => null,
    ]);

    Socialite::fake('github', (new SocialiteUser)->map([
        'id' => 'github-2',
        'name' => 'Existing User',
        'nickname' => 'existing',
        'email' => 'existing@example.com',
        'avatar' => 'https://github.com/avatar.png',
    ]));

    $this->get('/auth/github/callback')->assertRedirect(route('admin.home'));

    $user->refresh();

    expect($user->current_tenant_id)->not->toBeNull()
        ->and(Tenant::where('user_id', $user->id)->exists())->toBeTrue();
});

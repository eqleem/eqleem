<?php

use App\Livewire\Tenant\Cart\Badge;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
});

it('renders a loading indicator for cart navigation', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Cart Badge Tenant',
        'handle' => 'cart-badge-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    Livewire::test(Badge::class)
        ->assertSee('x-on:click="loading = true"', false)
        ->assertSee('animate-spin', false)
        ->assertSee('solar:refresh-bold-duotone', false)
        ->assertSee(route('tenant.pages.cart'), false);
});

it('shows the cart item count when the cart is not empty', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Cart Badge Tenant',
        'handle' => 'cart-badge-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    Livewire::test(Badge::class)
        ->set('count', 3)
        ->assertSee('3', false)
        ->assertSee('سلة المشتريات، 3 عناصر', false);
});

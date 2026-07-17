<?php

use App\Livewire\Tenant\Blocks\TopNav;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);

    view()->prependNamespace('tenant-theme', public_path('themes/default'));
    view()->prependNamespace('default-tenant-theme', public_path('themes/default'));
});

function createTenantForTopNavBrandMarkSizing(): Tenant
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Top Nav Mark Tenant',
        'handle' => 'top-nav-mark-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    return $tenant->fresh();
}

it('renders emoji brand marks smaller in the top nav back button', function () {
    $tenant = createTenantForTopNavBrandMarkSizing();
    $profile = app(TenantProfileService::class);

    $profile->saveBrandMark($tenant, [
        'type' => 'emoji',
        'value' => '🚀',
    ]);

    setCurrentTenant($tenant->fresh());

    $this->get(route('tenant.blog.index', ['tenant' => $tenant->handle]))
        ->assertSuccessful();

    Livewire::test(TopNav::class)
        ->assertSuccessful()
        ->assertSee('font-size: var(--brand-mark-icon-size, 1.35rem)', false)
        ->assertSee('🚀', false);
});

it('keeps a larger icon size for non-emoji top nav brand marks', function () {
    $tenant = createTenantForTopNavBrandMarkSizing();
    $profile = app(TenantProfileService::class);

    $profile->saveBrandMark($tenant, [
        'type' => 'icon',
        'value' => 'tabler:home',
        'color' => '#2563eb',
    ]);

    setCurrentTenant($tenant->fresh());

    $this->get(route('tenant.blog.index', ['tenant' => $tenant->handle]))
        ->assertSuccessful();

    Livewire::test(TopNav::class)
        ->assertSuccessful()
        ->assertSee('font-size: var(--brand-mark-icon-size, 1.75rem)', false);
});

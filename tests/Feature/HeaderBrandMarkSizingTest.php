<?php

use App\Livewire\Tenant\Blocks\Header;
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

function createTenantForHeaderBrandMarkSizing(): Tenant
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Header Mark Tenant',
        'handle' => 'header-mark-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    return $tenant->fresh();
}

it('renders image brand marks with a smaller logo size than icon marks', function () {
    $tenant = createTenantForHeaderBrandMarkSizing();
    $profile = app(TenantProfileService::class);

    $profile->saveLogo($tenant, 'tenants/logos/example.png');
    setCurrentTenant($tenant->fresh());

    Livewire::test(Header::class)
        ->assertSuccessful()
        ->assertSee('size-14 md:size-[4.5rem]', false)
        ->assertDontSee('size-24', false);
});

it('keeps the larger container size for icon brand marks', function () {
    $tenant = createTenantForHeaderBrandMarkSizing();
    $profile = app(TenantProfileService::class);

    $profile->saveBrandMark($tenant, [
        'type' => 'icon',
        'value' => 'tabler:home',
        'color' => '#2563eb',
    ]);

    setCurrentTenant($tenant->fresh());

    expect($profile->brandMark(tenant())['type'])->toBe('icon');

    Livewire::test(Header::class)
        ->assertSuccessful()
        ->assertSee('size-24', false)
        ->assertDontSee('size-14 md:size-[4.5rem]', false);
});

it('gives header social links accessible names', function () {
    $tenant = createTenantForHeaderBrandMarkSizing();
    $profile = app(TenantProfileService::class);

    $profile->addSocialLink($tenant, 'twitter', 'https://x.com/eqleem');
    setCurrentTenant($tenant->fresh());

    Livewire::test(Header::class)
        ->assertSuccessful()
        ->assertSeeHtml('aria-label="X (تويتر)"')
        ->assertSee('https://x.com/eqleem', false);
});

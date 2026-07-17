<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use App\Services\TenantProfileService;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);

    view()->prependNamespace('tenant-theme', public_path('themes/default'));
    view()->prependNamespace('default-tenant-theme', public_path('themes/default'));
});

/**
 * @return array{0: Tenant, 1: string}
 */
function createTenantForMetaDescription(): array
{
    $owner = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $theme = Theme::query()->where('slug', 'default')->firstOrFail();

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر الوصف',
        'handle' => 'meta-desc-'.Str::lower(Str::random(6)),
        'user_id' => $owner->id,
        'theme_id' => $theme->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    $bio = 'نقدم تشطيبات وديكور منزل بمعايير عالية الجودة.';

    app(TenantProfileService::class)->saveBio($tenant, $bio);

    return [$tenant->fresh(), $bio];
}

it('includes a meta description on the tenant home page from tenant bio', function () {
    [$tenant, $bio] = createTenantForMetaDescription();

    $this->get(route('tenant.home', ['tenant' => $tenant->handle]))
        ->assertSuccessful()
        ->assertSee('<meta name="description" content="'.$bio.'">', false);
});

it('falls back to the tenant name when bio is empty', function () {
    [$tenant] = createTenantForMetaDescription();

    app(TenantProfileService::class)->saveBio($tenant, '');

    $this->get(route('tenant.home', ['tenant' => $tenant->handle]))
        ->assertSuccessful()
        ->assertSee('<meta name="description" content="متجر الوصف">', false);
});

it('builds meta descriptions via the helper', function () {
    expect(tenantMetaDescription('وصف مختصر'))->toBe('وصف مختصر')
        ->and(tenantMetaDescription(null))->toBe((string) config('app.name'));
});

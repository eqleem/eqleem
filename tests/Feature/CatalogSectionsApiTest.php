<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Tenant;
use App\Models\User;
use App\Support\ContentTypeRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForCatalogSectionsApi(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'catalog-sections-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('owner can list catalog sections with default enabled flags', function () {
    [$user] = createUserWithTenantForCatalogSectionsApi();

    $expectedSlugs = app(ContentTypeRegistry::class)->configured()
        ->filter(fn ($type): bool => $type->sellable)
        ->pluck('slug')
        ->values()
        ->all();

    $response = $this->actingAs($user)
        ->getJson('/api/page/catalog-sections')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => ['slug', 'name', 'description', 'icon', 'enabled'],
            ],
            'enabled',
        ]);

    $slugs = collect($response->json('data'))->pluck('slug')->values()->all();

    expect($slugs)->toBe($expectedSlugs)
        ->and($response->json('enabled'))->toContain('store')
        ->and($response->json('enabled'))->not->toContain('courses');
});

test('owner can save enabled catalog sections and nav list updates', function () {
    [$user, $tenant] = createUserWithTenantForCatalogSectionsApi();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => ['store', 'services'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('enabled', ['store', 'services']);

    expect(data_get($tenant->fresh()->config, 'enabled_content_types'))->toBe(['store', 'services']);

    $navSlugs = $this->actingAs($user)
        ->getJson('/api/page/content-types')
        ->assertSuccessful()
        ->json('data.*.slug');

    expect($navSlugs)->toContain('store', 'services', 'blog')
        ->and($navSlugs)->not->toContain('courses', 'digital-products');
});

test('owner can disable all sellable catalog sections', function () {
    [$user] = createUserWithTenantForCatalogSectionsApi();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => [],
        ])
        ->assertSuccessful()
        ->assertJsonPath('enabled', []);

    $sellableSlugs = collect($this->actingAs($user)
        ->getJson('/api/page/content-types')
        ->assertSuccessful()
        ->json('data'))
        ->where('sellable', true)
        ->pluck('slug')
        ->all();

    expect($sellableSlugs)->toBeEmpty();
});

test('guest cannot list or save catalog sections', function () {
    $this->getJson('/api/page/catalog-sections')->assertUnauthorized();
    $this->putJson('/api/page/catalog-sections', ['enabled' => ['store']])->assertUnauthorized();
});

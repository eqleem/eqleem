<?php

use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant, 2: Content}
 */
function createUserTenantAndCatalogItemForToggle(string $typeSlug, string $title): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'كتالوج تفعيل',
        'handle' => 'catalog-toggle-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    $item = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel($typeSlug),
        'title' => $title,
        'slug' => 'demo-'.Str::lower(Str::random(4)),
        'data' => [],
        'active' => true,
        'status' => 'published',
        'published_at' => now(),
    ]);

    return [$user->fresh(), $tenant->fresh(), $item->fresh()];
}

dataset('catalogToggleEndpoints', [
    'store' => ['store', '/api/store', 'منتج متجر'],
    'digital-products' => ['digital-products', '/api/digital-products', 'منتج رقمي'],
    'services' => ['services', '/api/services', 'خدمة بالساعة'],
    'on-demand-services' => ['on-demand-services', '/api/on-demand-services', 'خدمة حسب الطلب'],
]);

test('owner can toggle catalog item active state', function (string $typeSlug, string $endpoint, string $title) {
    [$user, $tenant, $item] = createUserTenantAndCatalogItemForToggle($typeSlug, $title);

    $this->actingAs($user)
        ->putJson("{$endpoint}/{$item->uuid}/active", ['active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false)
        ->assertJsonPath('data.published', false)
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.slug', $item->slug);

    expect($item->fresh())
        ->active->toBeFalse()
        ->status->toBe('draft')
        ->published_at->toBeNull();

    $this->actingAs($user)
        ->putJson("{$endpoint}/{$item->uuid}/active", ['active' => true])
        ->assertSuccessful()
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.published', true)
        ->assertJsonPath('data.status', 'published');

    expect($item->fresh())
        ->active->toBeTrue()
        ->status->toBe('published')
        ->published_at->not->toBeNull();
})->with('catalogToggleEndpoints');

test('guests cannot toggle catalog item active state', function (string $typeSlug, string $endpoint, string $title) {
    [, , $item] = createUserTenantAndCatalogItemForToggle($typeSlug, $title);

    $this->putJson("{$endpoint}/{$item->uuid}/active", ['active' => false])
        ->assertUnauthorized();
})->with('catalogToggleEndpoints');

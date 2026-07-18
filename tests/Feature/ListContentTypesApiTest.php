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
function createUserWithTenantForContentTypesApi(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'content-types-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('owner can list active content types for page nav', function () {
    [$user] = createUserWithTenantForContentTypesApi();

    $expectedSlugs = app(ContentTypeRegistry::class)->all()->pluck('slug')->values()->all();

    $response = $this->actingAs($user)
        ->getJson('/api/page/content-types')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'slug', 'label', 'icon', 'sellable', 'content_type'],
            ],
        ]);

    $slugs = collect($response->json('data'))->pluck('slug')->values()->all();
    $bySellable = collect($response->json('data'))->groupBy(fn (array $tab) => $tab['sellable'] ? 'sellable' : 'content');

    expect($slugs)->toBe($expectedSlugs)
        ->and($slugs)->toContain('pages', 'forms', 'blog', 'store')
        ->and($slugs)->not->toContain('courses', 'newsletter', 'digital-services')
        ->and($bySellable->get('content')?->pluck('slug')->all() ?? [])->toContain('pages', 'forms', 'blog', 'portfolio')
        ->and($bySellable->get('sellable')?->pluck('slug')->all() ?? [])->toContain('store');
});

test('inactive content types are omitted from the list', function () {
    [$user] = createUserWithTenantForContentTypesApi();

    config([
        'content-types.digital-services.active' => false,
    ]);

    $slugs = $this->actingAs($user)
        ->getJson('/api/page/content-types')
        ->assertSuccessful()
        ->json('data.*.slug');

    expect($slugs)->not->toContain('digital-services')
        ->and($slugs)->toContain('blog', 'store');
});

test('guest cannot list content types', function () {
    $this->getJson('/api/page/content-types')->assertUnauthorized();
});

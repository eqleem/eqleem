<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Block;
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

test('owner can list all page sections grouped by config with default enabled flags', function () {
    [$user] = createUserWithTenantForCatalogSectionsApi();

    $expectedSlugs = app(ContentTypeRegistry::class)->managedSections()
        ->pluck('slug')
        ->values()
        ->all();

    $response = $this->actingAs($user)
        ->getJson('/api/page/catalog-sections')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => ['slug', 'name', 'description', 'icon', 'section', 'enabled'],
            ],
            'enabled',
        ]);

    $slugs = collect($response->json('data'))->pluck('slug')->values()->all();

    expect($slugs)->toBe($expectedSlugs)
        ->and($slugs)->not->toContain('pages', 'forms')
        ->and($response->json('data.0.section'))->toBe('content')
        ->and($response->json('enabled'))->toContain('store')
        ->and($response->json('enabled'))->not->toContain('courses');
});

test('owner can save enabled sections and synchronize nav and page links', function () {
    [$user, $tenant] = createUserWithTenantForCatalogSectionsApi();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => ['blog', 'store', 'services'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('enabled', ['blog', 'store', 'services']);

    $tenantConfig = $tenant->fresh()->config;

    expect(data_get($tenantConfig, 'enabled_content_types'))->toBe(['blog', 'store', 'services'])
        ->and(data_get($tenantConfig, 'page_sections_configured'))->toBeTrue();

    $navSlugs = $this->actingAs($user)
        ->getJson('/api/page/content-types')
        ->assertSuccessful()
        ->json('data.*.slug');

    expect($navSlugs)->toContain('pages', 'forms', 'blog', 'store', 'services');

    setCurrentTenant($tenant->fresh());

    $sectionBlocks = Block::queryForTenantRoots()
        ->userBlocks()
        ->where('type', 'block-link')
        ->where('data->link_type', 'section')
        ->get()
        ->keyBy('data.content_type');

    expect($sectionBlocks)->toHaveKeys(['blog', 'store', 'services'])
        ->and($sectionBlocks->get('blog')?->active)->toBeTrue()
        ->and($sectionBlocks->get('store')?->active)->toBeTrue()
        ->and($sectionBlocks->get('services')?->active)->toBeTrue();
});

test('reviews section is offered under the trust group and can be enabled', function () {
    [$user, $tenant] = createUserWithTenantForCatalogSectionsApi();

    $option = collect($this->actingAs($user)
        ->getJson('/api/page/catalog-sections')
        ->assertSuccessful()
        ->json('data'))
        ->firstWhere('slug', 'reviews');

    expect($option)->not->toBeNull()
        ->and($option['name'])->toBe('التقييمات')
        ->and($option['section'])->toBe('trust')
        ->and($option['enabled'])->toBeFalse();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => ['store', 'reviews'],
        ])
        ->assertSuccessful();

    $navSlugs = $this->actingAs($user)
        ->getJson('/api/page/content-types')
        ->assertSuccessful()
        ->json('data.*.slug');

    expect($navSlugs)->toContain('reviews');

    setCurrentTenant($tenant->fresh());

    $reviewsBlock = Block::queryForTenantRoots()
        ->userBlocks()
        ->where('type', 'block-link')
        ->where('data->link_type', 'section')
        ->where('data->content_type', 'reviews')
        ->first();

    expect($reviewsBlock)->not->toBeNull()
        ->and($reviewsBlock->active)->toBeTrue()
        ->and($reviewsBlock->title)->toBe('التقييمات');
});

test('owner can disable all sections and their page links', function () {
    [$user, $tenant] = createUserWithTenantForCatalogSectionsApi();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => ['blog', 'store'],
        ])
        ->assertSuccessful();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => [],
        ])
        ->assertSuccessful()
        ->assertJsonPath('enabled', []);

    $navSlugs = $this->actingAs($user)
        ->getJson('/api/page/content-types')
        ->assertSuccessful()
        ->json('data.*.slug');

    setCurrentTenant($tenant->fresh());

    $activeSectionLinks = Block::queryForTenantRoots()
        ->userBlocks()
        ->where('type', 'block-link')
        ->where('data->link_type', 'section')
        ->where('active', true)
        ->count();

    expect($navSlugs)->toBe(['pages', 'forms'])
        ->and($activeSectionLinks)->toBe(0);
});

test('legacy catalog preferences do not disable non-sellable sections', function () {
    [$user, $tenant] = createUserWithTenantForCatalogSectionsApi();

    $tenant->update([
        'config' => ['enabled_content_types' => ['store']],
    ]);

    $navSlugs = $this->actingAs($user)
        ->getJson('/api/page/content-types')
        ->assertSuccessful()
        ->json('data.*.slug');

    expect($navSlugs)->toContain('pages', 'blog', 'portfolio', 'store');
});

test('saving enabled sections does not reactivate manually disabled blocks', function () {
    [$user, $tenant] = createUserWithTenantForCatalogSectionsApi();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => ['blog', 'store'],
        ])
        ->assertSuccessful();

    setCurrentTenant($tenant->fresh());

    $blogBlock = Block::queryForTenantRoots()
        ->userBlocks()
        ->where('type', 'block-link')
        ->where('data->link_type', 'section')
        ->where('data->content_type', 'blog')
        ->firstOrFail();

    $blogBlock->update(['active' => false]);

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => ['blog', 'store'],
        ])
        ->assertSuccessful();

    expect($blogBlock->fresh()->active)->toBeFalse()
        ->and(data_get($blogBlock->fresh()->data, 'disabled_by_section_manager'))->toBeNull();
});

test('re-enabling a section restores blocks disabled by the section manager', function () {
    [$user, $tenant] = createUserWithTenantForCatalogSectionsApi();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => ['blog'],
        ])
        ->assertSuccessful();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => [],
        ])
        ->assertSuccessful();

    setCurrentTenant($tenant->fresh());

    $blogBlock = Block::queryForTenantRoots()
        ->userBlocks()
        ->where('type', 'block-link')
        ->where('data->link_type', 'section')
        ->where('data->content_type', 'blog')
        ->firstOrFail();

    expect($blogBlock->active)->toBeFalse()
        ->and(data_get($blogBlock->data, 'disabled_by_section_manager'))->toBeTrue();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => ['blog'],
        ])
        ->assertSuccessful();

    $blogBlock = $blogBlock->fresh();

    expect($blogBlock->active)->toBeTrue()
        ->and(data_get($blogBlock->data, 'disabled_by_section_manager'))->toBeNull()
        ->and(data_get($blogBlock->data, 'managed_section'))->toBeTrue();
});

test('guest cannot list or save page sections', function () {
    $this->getJson('/api/page/catalog-sections')->assertUnauthorized();
    $this->putJson('/api/page/catalog-sections', ['enabled' => ['store']])->assertUnauthorized();
});

test('permanent content types cannot be submitted to section management', function () {
    [$user] = createUserWithTenantForCatalogSectionsApi();

    $this->actingAs($user)
        ->putJson('/api/page/catalog-sections', [
            'enabled' => ['pages', 'forms'],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['enabled.0', 'enabled.1']);
});

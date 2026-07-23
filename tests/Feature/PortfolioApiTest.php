<?php

use App\Models\Content;
use App\Models\Setting;
use App\Models\Taxonomy;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForPortfolio(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'معرض تجريبي',
        'handle' => 'portfolio-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access portfolio endpoints', function () {
    $this->getJson('/api/portfolio')->assertUnauthorized();
    $this->postJson('/api/portfolio', ['title' => 'مشروع'])->assertUnauthorized();
    $this->getJson('/api/portfolio/categories')->assertUnauthorized();
    $this->getJson('/api/portfolio/settings')->assertUnauthorized();

    $uuid = (string) Str::uuid();
    $this->putJson("/api/portfolio/{$uuid}/active", ['active' => false])->assertUnauthorized();
    $this->postJson("/api/portfolio/{$uuid}/clone")->assertUnauthorized();
});

test('owner can create list update clone toggle and delete portfolio projects', function () {
    [$user, $tenant] = createUserWithTenantForPortfolio();

    $create = $this->actingAs($user)
        ->postJson('/api/portfolio', ['title' => 'فيلا نجدية'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'فيلا نجدية')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.published', false);

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->getJson('/api/portfolio')
        ->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $uuid)
        ->assertJsonPath('meta.total', 1);

    $this->actingAs($user)
        ->getJson("/api/portfolio/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'فيلا نجدية')
        ->assertJsonStructure([
            'data' => [
                'category_options',
                'images',
                'slug_prefix',
            ],
        ]);

    setCurrentTenant($tenant);

    $parent = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'تصميم داخلي',
        'type' => 'portfolio_category',
        'sort_order' => 0,
    ]);

    $leaf = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'فلل',
        'type' => 'portfolio_category',
        'parent_id' => $parent->id,
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->putJson("/api/portfolio/{$uuid}", [
            'title' => 'فيلا نجدية محدثة',
            'subtitle' => 'وصف فرعي',
            'body' => '<p>محتوى المشروع</p>',
            'slug' => 'villa-najd',
            'category_ids' => [$leaf->id],
            'published' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'فيلا نجدية محدثة')
        ->assertJsonPath('data.published', true)
        ->assertJsonPath('data.subtitle', 'وصف فرعي')
        ->assertJsonPath('data.category_ids.0', (string) $leaf->id);

    setCurrentTenant($tenant);

    $project = Content::query()->where('uuid', $uuid)->first();

    expect($project)->not->toBeNull()
        ->and($project->status)->toBe('published')
        ->and($project->active)->toBeTrue()
        ->and($project->published_at)->not->toBeNull();

    $clone = $this->actingAs($user)
        ->postJson("/api/portfolio/{$uuid}/clone")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'فيلا نجدية محدثة ٢')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.published', false);

    $cloneUuid = (string) $clone->json('data.uuid');

    expect($cloneUuid)->not->toBe($uuid);

    $this->actingAs($user)
        ->putJson("/api/portfolio/{$uuid}/active", ['active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false)
        ->assertJsonPath('data.published', false)
        ->assertJsonPath('data.status', 'draft');

    expect($project->fresh())
        ->active->toBeFalse()
        ->status->toBe('draft')
        ->published_at->toBeNull();

    $this->actingAs($user)
        ->putJson("/api/portfolio/{$uuid}/active", ['active' => true])
        ->assertSuccessful()
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.status', 'published');

    $this->actingAs($user)
        ->deleteJson('/api/portfolio', ['ids' => [$project->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.deleted', 1);

    setCurrentTenant($tenant);

    expect(Content::query()->where('uuid', $uuid)->exists())->toBeFalse();
});

test('owner can manage portfolio categories and reorder them', function () {
    [$user, $tenant] = createUserWithTenantForPortfolio();

    $first = $this->actingAs($user)
        ->postJson('/api/portfolio/categories', ['name' => 'هوية بصرية'])
        ->assertSuccessful()
        ->assertJsonPath('data.category.name', 'هوية بصرية');

    $firstId = (int) $first->json('data.category.id');

    $second = $this->actingAs($user)
        ->postJson('/api/portfolio/categories', [
            'name' => 'تصميم داخلي',
            'parent_id' => null,
        ])
        ->assertSuccessful();

    $secondId = (int) $second->json('data.category.id');

    $child = $this->actingAs($user)
        ->postJson('/api/portfolio/categories', [
            'name' => 'فلل سكنية',
            'parent_id' => $secondId,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.category.parent_id', $secondId);

    $this->actingAs($user)
        ->getJson('/api/portfolio/categories')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data.categories');

    setCurrentTenant($tenant);
    $slug = Taxonomy::query()->findOrFail($firstId)->slug;

    $this->actingAs($user)
        ->putJson("/api/portfolio/categories/{$firstId}", [
            'name' => 'هوية محدثة',
            'slug' => $slug,
            'parent_id' => null,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.category.name', 'هوية محدثة');

    $this->actingAs($user)
        ->putJson('/api/portfolio/categories/reorder', [
            'order' => [$secondId, $firstId, (int) $child->json('data.category.id')],
        ])
        ->assertSuccessful();

    setCurrentTenant($tenant);

    expect((int) Taxonomy::query()->findOrFail($secondId)->sort_order)->toBe(0)
        ->and((int) Taxonomy::query()->findOrFail($firstId)->sort_order)->toBe(1);

    $this->actingAs($user)
        ->deleteJson("/api/portfolio/categories/{$firstId}")
        ->assertSuccessful();

    setCurrentTenant($tenant);

    expect(Taxonomy::query()->whereKey($firstId)->exists())->toBeFalse();
});

test('owner can get and update portfolio settings', function () {
    [$user] = createUserWithTenantForPortfolio();

    $this->actingAs($user)
        ->getJson('/api/portfolio/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'معرض الأعمال');

    $this->actingAs($user)
        ->putJson('/api/portfolio/settings', [
            'section_title' => 'أعمالنا',
            'section_description' => 'مشاريع مختارة من معرض الأعمال',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'أعمالنا')
        ->assertJsonPath('data.section_description', 'مشاريع مختارة من معرض الأعمال');

    expect(Setting::portfolioSettings()['section_title'])->toBe('أعمالنا');
});

test('owner can upload reorder and delete portfolio gallery images', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user] = createUserWithTenantForPortfolio();

    $create = $this->actingAs($user)
        ->postJson('/api/portfolio', ['title' => 'مشروع صور'])
        ->assertSuccessful();

    $uuid = (string) $create->json('data.uuid');

    $upload = $this->actingAs($user)
        ->post("/api/portfolio/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('one.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $mediaId = (int) $upload->json('data.images.0.id');

    $this->actingAs($user)
        ->post("/api/portfolio/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('two.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $images = $this->actingAs($user)
        ->getJson("/api/portfolio/{$uuid}")
        ->assertSuccessful()
        ->json('data.images');

    expect($images)->toHaveCount(2);

    $reversed = array_reverse(array_column($images, 'id'));

    $this->actingAs($user)
        ->putJson("/api/portfolio/{$uuid}/images/reorder", ['order' => $reversed])
        ->assertSuccessful()
        ->assertJsonPath('data.images.0.id', $reversed[0]);

    $this->actingAs($user)
        ->deleteJson("/api/portfolio/{$uuid}/images/{$mediaId}")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.images');
});

test('project search filters by title', function () {
    [$user, $tenant] = createUserWithTenantForPortfolio();

    setCurrentTenant($tenant);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('portfolio'),
        'title' => 'Cafe Rawda',
        'slug' => 'cafe-rawda',
        'status' => 'draft',
        'active' => true,
    ]);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('portfolio'),
        'title' => 'Villa Najd',
        'slug' => 'villa',
        'status' => 'draft',
        'active' => true,
    ]);

    $this->actingAs($user)
        ->getJson('/api/portfolio?search=Cafe')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Cafe Rawda');
});

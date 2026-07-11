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
function createUserWithTenantForBlog(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'مدونة تجريبية',
        'handle' => 'blog-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access blog endpoints', function () {
    $this->getJson('/api/blog')->assertUnauthorized();
    $this->postJson('/api/blog', ['title' => 'تدوينة'])->assertUnauthorized();
    $this->getJson('/api/blog/categories')->assertUnauthorized();
    $this->getJson('/api/blog/settings')->assertUnauthorized();
});

test('owner can create list update and delete blog posts', function () {
    [$user, $tenant] = createUserWithTenantForBlog();

    $create = $this->actingAs($user)
        ->postJson('/api/blog', ['title' => 'أول تدوينة'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'أول تدوينة')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.published', false);

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->getJson('/api/blog')
        ->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $uuid)
        ->assertJsonPath('meta.total', 1);

    $this->actingAs($user)
        ->getJson("/api/blog/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'أول تدوينة')
        ->assertJsonStructure([
            'data' => [
                'category_options',
                'featured_image',
                'slug_prefix',
            ],
        ]);

    setCurrentTenant($tenant);

    $parent = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'تقنية',
        'type' => 'blog_category',
        'sort_order' => 0,
    ]);

    $leaf = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'برمجة',
        'type' => 'blog_category',
        'parent_id' => $parent->id,
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->putJson("/api/blog/{$uuid}", [
            'title' => 'أول تدوينة محدثة',
            'subtitle' => 'عنوان فرعي',
            'body' => '<p>محتوى التدوينة</p>',
            'slug' => 'first-post',
            'category_ids' => [$leaf->id],
            'published' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'أول تدوينة محدثة')
        ->assertJsonPath('data.published', true)
        ->assertJsonPath('data.subtitle', 'عنوان فرعي')
        ->assertJsonPath('data.category_ids.0', (string) $leaf->id);

    setCurrentTenant($tenant);

    $post = Content::query()->where('uuid', $uuid)->first();

    expect($post)->not->toBeNull()
        ->and($post->status)->toBe('published')
        ->and($post->published_at)->not->toBeNull();

    $this->actingAs($user)
        ->deleteJson('/api/blog', ['ids' => [$post->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.deleted', 1);

    setCurrentTenant($tenant);

    expect(Content::query()->where('uuid', $uuid)->exists())->toBeFalse();
});

test('owner can manage blog categories and reorder them', function () {
    [$user, $tenant] = createUserWithTenantForBlog();

    $first = $this->actingAs($user)
        ->postJson('/api/blog/categories', ['name' => 'أخبار'])
        ->assertSuccessful()
        ->assertJsonPath('data.category.name', 'أخبار');

    $firstId = (int) $first->json('data.category.id');

    $second = $this->actingAs($user)
        ->postJson('/api/blog/categories', [
            'name' => 'تقنية',
            'parent_id' => null,
        ])
        ->assertSuccessful();

    $secondId = (int) $second->json('data.category.id');

    $child = $this->actingAs($user)
        ->postJson('/api/blog/categories', [
            'name' => 'برمجة',
            'parent_id' => $secondId,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.category.parent_id', $secondId);

    $this->actingAs($user)
        ->getJson('/api/blog/categories')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data.categories');

    setCurrentTenant($tenant);
    $slug = Taxonomy::query()->findOrFail($firstId)->slug;

    $this->actingAs($user)
        ->putJson("/api/blog/categories/{$firstId}", [
            'name' => 'أخبار محدثة',
            'slug' => $slug,
            'parent_id' => null,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.category.name', 'أخبار محدثة');

    $this->actingAs($user)
        ->putJson('/api/blog/categories/reorder', [
            'order' => [$secondId, $firstId, (int) $child->json('data.category.id')],
        ])
        ->assertSuccessful();

    setCurrentTenant($tenant);

    expect((int) Taxonomy::query()->findOrFail($secondId)->sort_order)->toBe(0)
        ->and((int) Taxonomy::query()->findOrFail($firstId)->sort_order)->toBe(1);

    $this->actingAs($user)
        ->deleteJson("/api/blog/categories/{$firstId}")
        ->assertSuccessful();

    setCurrentTenant($tenant);

    expect(Taxonomy::query()->whereKey($firstId)->exists())->toBeFalse();
});

test('owner can get and update blog settings', function () {
    [$user] = createUserWithTenantForBlog();

    $this->actingAs($user)
        ->getJson('/api/blog/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'المدونة');

    $this->actingAs($user)
        ->putJson('/api/blog/settings', [
            'section_title' => 'مدونتنا',
            'section_description' => 'مقالات وتدوينات متخصصة',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'مدونتنا')
        ->assertJsonPath('data.section_description', 'مقالات وتدوينات متخصصة');

    expect(Setting::blogSettings()['section_title'])->toBe('مدونتنا');
});

test('owner can upload and delete blog featured image', function () {
    Storage::fake('spaces');

    [$user] = createUserWithTenantForBlog();

    $create = $this->actingAs($user)
        ->postJson('/api/blog', ['title' => 'تدوينة بصورة'])
        ->assertSuccessful();

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->post("/api/blog/{$uuid}/featured-image", [
            'file' => UploadedFile::fake()->image('cover.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.featured_image', fn (mixed $value): bool => filled($value));

    setCurrentTenant(Tenant::query()->first());

    $post = Content::query()->where('uuid', $uuid)->first();

    expect(data_get($post?->data, 'image'))->not->toBeNull();

    $this->actingAs($user)
        ->deleteJson("/api/blog/{$uuid}/featured-image")
        ->assertSuccessful()
        ->assertJsonPath('data.featured_image', null);

    expect(data_get($post?->fresh()->data, 'image'))->toBeNull();
});

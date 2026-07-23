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
function createUserTenantAndBlogPostForToggle(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'مدونة تفعيل',
        'handle' => 'blog-toggle-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    $post = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('blog'),
        'title' => 'تدوينة تجريبية',
        'slug' => 'demo-post-'.Str::lower(Str::random(4)),
        'data' => [],
        'active' => true,
        'status' => 'published',
        'published_at' => now(),
    ]);

    return [$user->fresh(), $tenant->fresh(), $post->fresh()];
}

test('owner can toggle blog post active state', function () {
    [$user, $tenant, $post] = createUserTenantAndBlogPostForToggle();

    $this->actingAs($user)
        ->putJson("/api/blog/{$post->uuid}/active", ['active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false)
        ->assertJsonPath('data.published', false)
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.slug', $post->slug);

    expect($post->fresh())
        ->active->toBeFalse()
        ->status->toBe('draft')
        ->published_at->toBeNull();

    $this->actingAs($user)
        ->putJson("/api/blog/{$post->uuid}/active", ['active' => true])
        ->assertSuccessful()
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.published', true)
        ->assertJsonPath('data.status', 'published');

    expect($post->fresh())
        ->active->toBeTrue()
        ->status->toBe('published')
        ->published_at->not->toBeNull();
});

test('guests cannot toggle blog post active state', function () {
    [, , $post] = createUserTenantAndBlogPostForToggle();

    $this->putJson("/api/blog/{$post->uuid}/active", ['active' => false])
        ->assertUnauthorized();
});

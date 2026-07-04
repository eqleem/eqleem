<?php

use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
});

/**
 * @return array{user: User, otherUser: User, tenant: Tenant, post: Content}
 */
function createBlogPreviewContext(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $otherUser = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'test-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $post = Content::query()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'type' => contentTypeModel('blog'),
        'title' => 'Draft Post',
        'slug' => 'draft-post',
        'status' => 'draft',
        'active' => true,
        'data' => [
            'body' => 'Preview body content',
        ],
    ]);

    return compact('user', 'otherUser', 'tenant', 'post');
}

it('allows the post owner to preview a draft blog post', function () {
    ['user' => $user, 'tenant' => $tenant, 'post' => $post] = createBlogPreviewContext();

    $this->actingAs($user)
        ->get("/{$tenant->handle}/blog/{$post->slug}?mod=preview")
        ->assertSuccessful()
        ->assertSee('Draft Post');
});

it('denies preview for non owners', function () {
    ['otherUser' => $otherUser, 'tenant' => $tenant, 'post' => $post] = createBlogPreviewContext();

    $this->actingAs($otherUser)
        ->get("/{$tenant->handle}/blog/{$post->slug}?mod=preview")
        ->assertNotFound();
});

it('denies preview for guests', function () {
    ['tenant' => $tenant, 'post' => $post] = createBlogPreviewContext();

    $this->get("/{$tenant->handle}/blog/{$post->slug}?mod=preview")
        ->assertNotFound();
});

it('denies draft blog posts without preview mode', function () {
    ['user' => $user, 'tenant' => $tenant, 'post' => $post] = createBlogPreviewContext();

    $this->actingAs($user)
        ->get("/{$tenant->handle}/blog/{$post->slug}")
        ->assertNotFound();
});

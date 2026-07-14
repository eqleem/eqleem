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
 * @return array{author: User, tenant: Tenant, post: Content}
 */
function createBlogAuthorContext(): array
{
    $author = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'سارة الكاتب',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر الاختبار',
        'handle' => 'blog-author-'.Str::lower(Str::random(6)),
        'user_id' => $author->id,
        'active' => true,
        'status' => 'active',
    ]);

    $post = Content::query()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $author->id,
        'type' => contentTypeModel('blog'),
        'title' => 'تدوينة الاختبار',
        'slug' => 'test-blog-post',
        'status' => 'published',
        'active' => true,
        'published_at' => now()->subDay(),
        'data' => [
            'subtitle' => 'ملخص قصير',
        ],
    ]);

    return compact('author', 'tenant', 'post');
}

it('shows the post author name instead of the tenant name on the blog index', function () {
    ['tenant' => $tenant] = createBlogAuthorContext();

    $this->get("/{$tenant->handle}/blog")
        ->assertSuccessful()
        ->assertSee('سارة الكاتب', false)
        ->assertSee('تدوينة الاختبار', false);
});

it('shows the post author name instead of the tenant name on the blog detail page', function () {
    ['author' => $author, 'tenant' => $tenant, 'post' => $post] = createBlogAuthorContext();

    $this->get("/{$tenant->handle}/blog/{$post->slug}")
        ->assertSuccessful()
        ->assertSee('سارة الكاتب', false)
        ->assertSee('تدوينة الاختبار', false)
        ->assertSee($author->image, false);
});

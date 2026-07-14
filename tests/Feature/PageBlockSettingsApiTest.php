<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Support\ContentTypeRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForPageBlockSettings(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'page-blocks-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('owner can show and update top-nav settings', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);
    $block = Block::findSingleton('top-nav');

    $this->actingAs($user)
        ->getJson('/api/page/blocks/'.$block->id)
        ->assertSuccessful()
        ->assertJsonPath('data.block.type', 'top-nav')
        ->assertJsonPath('data.editor.type', 'top-nav');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$block->id, [
            'show_share_button' => false,
            'show_theme_toggle' => true,
            'show_language_switcher' => false,
            'show_back_button' => true,
            'show_pages_menu' => true,
            'show_client_login' => true,
            'client_login_label' => 'دخول',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.show_share_button', false)
        ->assertJsonPath('data.editor.client_login_label', 'دخول');
});

test('owner can update float-links settings', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);
    $block = Block::findSingleton('float-links');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$block->id, [
            'position' => 'bottom-start',
            'show_whatsapp' => true,
            'whatsapp_number' => '966500000000',
            'show_phone' => false,
            'phone_number' => '',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.position', 'bottom-start')
        ->assertJsonPath('data.editor.show_whatsapp', true)
        ->assertJsonPath('data.editor.whatsapp_number', '966500000000')
        ->assertJsonMissingPath('data.editor.show_scroll_top');
});

test('owner can update header settings', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);
    $block = Block::findSingleton('header');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$block->id, [
            'name' => 'صفحة محدثة',
            'bio' => 'نبذة جديدة',
            'country' => 'السعودية',
            'city' => 'جدة',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.name', 'صفحة محدثة')
        ->assertJsonPath('data.editor.bio', 'نبذة جديدة')
        ->assertJsonPath('data.editor.city', 'جدة');
});

test('owner can manage cta links', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);
    $block = Block::findSingleton('cta');

    $create = $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id.'/links', [
            'link_type' => 'external',
            'label' => 'موقعنا',
            'url' => 'https://example.com',
            'icon' => 'hugeicons:link-04',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.label', 'موقعنا');

    $linkId = (int) $create->json('data.id');

    $second = $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id.'/links', [
            'link_type' => 'external',
            'label' => 'تواصل',
            'url' => 'https://example.com/contact',
            'icon' => 'hugeicons:link-04',
        ])
        ->assertSuccessful();

    $secondId = (int) $second->json('data.id');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$block->id.'/links/reorder', [
            'order' => [$secondId, $linkId],
        ])
        ->assertSuccessful();

    setCurrentTenant($tenant);
    expect(Content::query()->whereKey($secondId)->value('sort_order'))->toBe(1);
    expect(Content::query()->whereKey($linkId)->value('sort_order'))->toBe(2);

    $this->actingAs($user)
        ->deleteJson('/api/page/blocks/'.$block->id.'/links/'.$linkId)
        ->assertSuccessful();

    expect(Content::query()->whereKey($linkId)->exists())->toBeFalse();
});

test('cta block editor exposes link type picker options', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);
    $block = Block::findSingleton('cta');

    $this->actingAs($user)
        ->getJson('/api/page/blocks/'.$block->id)
        ->assertSuccessful()
        ->assertJsonPath('data.editor.type', 'cta')
        ->assertJsonStructure([
            'data' => [
                'editor' => [
                    'link_type_picker_options',
                ],
            ],
        ]);
});

test('cta link rejects section links for pages and forms', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);
    $block = Block::findSingleton('cta');

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id.'/links', [
            'link_type' => 'section:pages',
            'label' => 'الصفحات',
        ])
        ->assertUnprocessable();

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id.'/links', [
            'link_type' => 'section:forms',
            'label' => 'النماذج',
        ])
        ->assertUnprocessable();
});

test('cta link to pages requires an existing page item', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);
    $block = Block::findSingleton('cta');

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id.'/links', [
            'link_type' => 'item:pages',
            'label' => 'صفحة',
        ])
        ->assertUnprocessable()
        ->assertJsonFragment(['message' => 'يرجى اختيار عنصر صالح من القائمة.']);

    $page = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('pages'),
        'title' => 'من نحن',
        'slug' => 'about-us',
        'status' => 'published',
        'active' => true,
    ]);

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id.'/links', [
            'link_type' => 'item:pages',
            'label' => 'من نحن',
            'content_id' => $page->id,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.label', 'من نحن')
        ->assertJsonPath('data.data.link_type', 'item')
        ->assertJsonPath('data.data.content_type', 'pages')
        ->assertJsonPath('data.data.content_id', $page->id);
});

test('owner can create cta link to a specific content item', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);
    $block = Block::findSingleton('cta');

    $post = Content::query()->create([
        'tenant_id' => $tenant->id,
        'uuid' => (string) Str::uuid(),
        'type' => contentTypeModel('blog'),
        'title' => 'تدوينة CTA',
        'slug' => 'cta-post-'.Str::lower(Str::random(6)),
        'status' => 'published',
        'published_at' => now(),
        'active' => true,
    ]);

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id.'/links', [
            'link_type' => 'item:blog',
            'label' => 'اقرأ المقال',
            'content_id' => $post->id,
            'icon' => 'hugeicons:news-01',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.label', 'اقرأ المقال')
        ->assertJsonPath('data.data.link_type', 'item')
        ->assertJsonPath('data.data.content_type', 'blog')
        ->assertJsonPath('data.data.content_id', $post->id)
        ->assertJsonPath('data.icon', 'hugeicons:news-01');
});

test('owner can save emoji brand mark on a cta link', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);
    $block = Block::findSingleton('cta');

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id.'/links', [
            'link_type' => 'external',
            'label' => 'تواصل',
            'url' => 'https://example.com/contact',
            'brand_mark_type' => 'emoji',
            'brand_mark_value' => '👋',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.label', 'تواصل')
        ->assertJsonPath('data.brand_mark.type', 'emoji')
        ->assertJsonPath('data.brand_mark.value', '👋');

    setCurrentTenant($tenant);
    $link = Content::query()->where('block_id', $block->id)->type('cta-link')->latest('id')->first();

    expect(data_get($link?->data, 'brand_mark.type'))->toBe('emoji')
        ->and(data_get($link?->data, 'brand_mark.value'))->toBe('👋');
});

test('owner can update block-link settings', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    $create = $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'block-link'])
        ->assertSuccessful();

    $blockId = (int) $create->json('data.block.id');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$blockId, [
            'link_type' => 'section:blog',
            'title' => 'المدونة',
            'description' => 'اقرأ مقالاتنا',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.title', 'المدونة')
        ->assertJsonPath('data.block.title', 'المدونة')
        ->assertJsonPath('data.editor.link_type', 'section:blog');

    $this->actingAs($user)
        ->getJson('/api/page/blocks/'.$blockId)
        ->assertSuccessful()
        ->assertJsonPath('data.editor.link_type', 'section:blog')
        ->assertJsonStructure([
            'data' => [
                'editor' => [
                    'link_type_picker_options',
                ],
            ],
        ]);

    $picker = $this->actingAs($user)
        ->getJson('/api/page/blocks/'.$blockId)
        ->json('data.editor.link_type_picker_options');

    expect($picker)->toBeArray()->not->toBeEmpty();
    expect(collect($picker)->pluck('key'))->toContain('blog', 'store', 'external');
    expect(collect($picker)->where('group', 'content')->pluck('key'))
        ->not->toContain('item:blog');

    $activeContentTypeSlugs = app(ContentTypeRegistry::class)->all()->pluck('slug')->values();
    $contentPickerKeys = collect($picker)->where('group', 'content')->pluck('key')->values();

    expect($contentPickerKeys->sort()->values()->all())
        ->toBe($activeContentTypeSlugs->sort()->values()->all());
    expect($contentPickerKeys)->not->toContain('forms', 'courses', 'services', 'newsletter');
});

test('owner can update block-link to external url and specific item', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    $blockId = (int) $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'block-link'])
        ->json('data.block.id');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$blockId, [
            'link_type' => 'external',
            'title' => 'موقعنا',
            'description' => 'رابط خارجي',
            'url' => 'https://example.com',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.link_type', 'external')
        ->assertJsonPath('data.editor.url', 'https://example.com');

    setCurrentTenant($tenant);

    $post = Content::query()->create([
        'tenant_id' => $tenant->id,
        'uuid' => (string) Str::uuid(),
        'type' => contentTypeModel('blog'),
        'title' => 'تدوينة تجريبية',
        'slug' => 'test-post-'.Str::lower(Str::random(6)),
        'status' => 'published',
        'published_at' => now(),
        'active' => true,
    ]);

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$blockId, [
            'link_type' => 'item:blog',
            'title' => 'تدوينة تجريبية',
            'description' => 'اقرأ هذه التدوينة',
            'content_id' => $post->id,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.link_type', 'item:blog')
        ->assertJsonPath('data.editor.content_id', $post->id);
});

test('external block-link requires a full url', function () {
    [$user] = createUserWithTenantForPageBlockSettings();

    $blockId = (int) $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'block-link'])
        ->json('data.block.id');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$blockId, [
            'link_type' => 'external',
            'title' => 'بدون رابط',
        ])
        ->assertStatus(422);
});

test('owner can search specific content items for block-link picker', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);

    $post = Content::query()->create([
        'tenant_id' => $tenant->id,
        'uuid' => (string) Str::uuid(),
        'type' => contentTypeModel('blog'),
        'title' => 'دليل التشطيب',
        'slug' => 'finishing-guide-'.Str::lower(Str::random(6)),
        'status' => 'published',
        'published_at' => now(),
        'active' => true,
    ]);

    $this->actingAs($user)
        ->getJson('/api/page/link-content?link_type=item:blog&search=د')
        ->assertSuccessful()
        ->assertJsonFragment(['id' => $post->id, 'title' => 'دليل التشطيب']);

    $this->actingAs($user)
        ->getJson('/api/page/link-content?link_type=item:blog')
        ->assertSuccessful()
        ->assertJsonFragment(['id' => $post->id]);
});

test('owner can create block-link with settings in one request', function () {
    [$user] = createUserWithTenantForPageBlockSettings();

    $this->actingAs($user)
        ->postJson('/api/page/blocks', [
            'type' => 'block-link',
            'link_type' => 'section:blog',
            'title' => 'المدونة',
            'description' => 'اقرأ مقالاتنا',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.block.type', 'block-link')
        ->assertJsonPath('data.block.title', 'المدونة')
        ->assertJsonPath('data.editor.link_type', 'section:blog')
        ->assertJsonPath('data.editor.title', 'المدونة');
});

test('failed block-link create with settings does not leave an orphan block', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    setCurrentTenant($tenant);
    $before = Block::queryForTenantRoots()->userBlocks()->count();

    $this->actingAs($user)
        ->postJson('/api/page/blocks', [
            'type' => 'block-link',
            'link_type' => 'external',
            'title' => 'بدون رابط',
        ])
        ->assertStatus(422);

    setCurrentTenant($tenant);
    expect(Block::queryForTenantRoots()->userBlocks()->count())->toBe($before);
});

test('owner can save an emoji brand mark on a block-link', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    $blockId = (int) $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'block-link'])
        ->json('data.block.id');

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$blockId, [
            'link_type' => 'section:blog',
            'title' => 'المدونة',
            'description' => 'اقرأ مقالاتنا',
            'brand_mark_type' => 'emoji',
            'brand_mark_value' => '🚀',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.brand_mark.type', 'emoji')
        ->assertJsonPath('data.editor.brand_mark.value', '🚀');

    setCurrentTenant($tenant);
    $block = Block::query()->findOrFail($blockId);

    expect(data_get($block->data, 'brand_mark.type'))->toBe('emoji')
        ->and(data_get($block->data, 'brand_mark.value'))->toBe('🚀')
        ->and(data_get($tenant->fresh()->meta, 'brand_mark'))->toBeNull();
});

test('owner can save an icon brand mark on a block-link', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    $blockId = (int) $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'block-link'])
        ->json('data.block.id');

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$blockId, [
            'link_type' => 'section:blog',
            'title' => 'المدونة',
            'description' => 'اقرأ مقالاتنا',
            'brand_mark_type' => 'icon',
            'brand_mark_value' => 'tabler:home',
            'brand_mark_color' => '#DC2626',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.brand_mark.type', 'icon')
        ->assertJsonPath('data.editor.brand_mark.value', 'tabler:home')
        ->assertJsonPath('data.editor.brand_mark.color', '#dc2626');

    setCurrentTenant($tenant);
    $block = Block::query()->findOrFail($blockId);

    expect(data_get($block->data, 'brand_mark.type'))->toBe('icon')
        ->and(data_get($block->data, 'brand_mark.value'))->toBe('tabler:home')
        ->and(data_get($tenant->fresh()->meta, 'brand_mark'))->toBeNull();
});

test('owner can upload an image brand mark on a block-link', function () {
    Storage::fake('spaces');

    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    $blockId = (int) $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'block-link'])
        ->json('data.block.id');

    $this->actingAs($user)
        ->post('/api/page/blocks/'.$blockId, [
            'link_type' => 'section:blog',
            'title' => 'المدونة',
            'description' => 'اقرأ مقالاتنا',
            'brand_mark_type' => 'image',
            'logo' => UploadedFile::fake()->image('icon.jpg', 200, 200),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.brand_mark.type', 'image');

    setCurrentTenant($tenant);
    $block = Block::query()->findOrFail($blockId);

    expect(data_get($block->data, 'brand_mark.type'))->toBe('image')
        ->and(data_get($block->data, 'brand_mark.path'))->not->toBeEmpty()
        ->and(data_get($tenant->fresh()->meta, 'brand_mark'))->toBeNull()
        ->and(data_get($tenant->fresh()->meta, 'logo'))->toBeNull();
});

test('owner can remove a brand mark from a block-link', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    $blockId = (int) $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'block-link'])
        ->json('data.block.id');

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$blockId, [
            'link_type' => 'section:blog',
            'title' => 'المدونة',
            'brand_mark_type' => 'emoji',
            'brand_mark_value' => '🔥',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.brand_mark.type', 'emoji');

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$blockId, [
            'link_type' => 'section:blog',
            'title' => 'المدونة',
            'brand_mark_type' => 'none',
            'remove_logo' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.brand_mark', null);

    setCurrentTenant($tenant);
    $block = Block::query()->findOrFail($blockId);

    expect(data_get($block->data, 'brand_mark'))->toBeNull();
});

test('block-link editor returns null brand mark when unset', function () {
    [$user] = createUserWithTenantForPageBlockSettings();

    $blockId = (int) $this->actingAs($user)
        ->postJson('/api/page/blocks', [
            'type' => 'block-link',
            'link_type' => 'section:blog',
            'title' => 'المدونة',
        ])
        ->json('data.block.id');

    $this->actingAs($user)
        ->getJson('/api/page/blocks/'.$blockId)
        ->assertSuccessful()
        ->assertJsonPath('data.editor.brand_mark', null);
});

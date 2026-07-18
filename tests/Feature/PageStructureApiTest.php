<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Block;
use App\Models\Tenant;
use App\Models\User;
use App\Support\BlockTypeRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForPageStructure(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'page-structure-'.Str::lower(Str::random(6)),
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

test('guests cannot access page structure', function () {
    $this->getJson('/api/page/structure')->assertUnauthorized();
    $this->postJson('/api/page/blocks', ['type' => 'block-link'])->assertUnauthorized();
});

test('owner can list page structure with system and user blocks', function () {
    [$user, $tenant] = createUserWithTenantForPageStructure();

    setCurrentTenant($tenant);

    Block::findSingleton('header')?->update(['title' => 'رأس الصفحة']);

    $block = Block::query()->create([
        'tenant_id' => $tenant->id,
        'component' => 'tenant::components.block-link',
        'type' => 'block-link',
        'title' => 'بطاقة تجريبية',
        'sort_order' => 99,
        'is_default' => false,
        'status' => 'draft',
        'active' => true,
        'position' => 'home',
    ]);

    $this->actingAs($user)
        ->getJson('/api/page/structure')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'top_blocks',
                'cta_block',
                'user_blocks',
                'bottom_blocks',
                'float_links_block',
                'block_types',
                'block_link_editor',
            ],
        ])
        ->assertJsonFragment(['id' => $block->id, 'title' => 'بطاقة تجريبية'])
        ->assertJsonPath('data.block_types.0.slug', 'block-link')
        ->assertJsonPath('data.block_link_editor.type', 'block-link')
        ->assertJsonPath('data.top_blocks.0.type', 'top-nav')
        ->assertJsonPath('data.top_blocks.1.title', 'معلومات النشاط')
        ->assertJsonPath('data.cta_block.type', 'cta')
        ->assertJsonPath('data.cta_block.title', 'الأزرار السريعة (هدف الصفحة)')
        ->assertJsonPath('data.float_links_block.type', 'float-links')
        ->assertJsonStructure([
            'data' => [
                'block_link_editor' => [
                    'type',
                    'link_type_picker_options',
                ],
                'cta_block' => [
                    'id',
                    'editor' => [
                        'type',
                        'links',
                        'link_type_options',
                        'link_type_picker_options',
                    ],
                ],
                'bottom_blocks' => [
                    '*' => [
                        'id',
                        'editor' => [
                            'type',
                            'links',
                            'link_type_options',
                            'link_type_picker_options',
                        ],
                    ],
                ],
                'float_links_block' => [
                    'id',
                    'editor' => [
                        'type',
                        'position',
                        'show_whatsapp',
                        'show_phone',
                    ],
                ],
            ],
        ])
        ->assertJsonPath('data.bottom_blocks.0.type', 'footer')
        ->assertJsonPath('data.bottom_blocks.0.editor.type', 'footer');
});

test('cta block is not included among locked top blocks', function () {
    [$user] = createUserWithTenantForPageStructure();

    $types = $this->actingAs($user)
        ->getJson('/api/page/structure')
        ->assertSuccessful()
        ->json('data.top_blocks.*.type');

    expect($types)->not->toContain('cta');
});

test('float links block is not included among locked bottom blocks', function () {
    [$user] = createUserWithTenantForPageStructure();

    $types = $this->actingAs($user)
        ->getJson('/api/page/structure')
        ->assertSuccessful()
        ->json('data.bottom_blocks.*.type');

    expect($types)->not->toContain('float-links');
    expect($types)->toContain('footer');
});
test('owner can create reorder toggle and delete user blocks', function () {
    [$user, $tenant] = createUserWithTenantForPageStructure();

    $create = $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'block-link'])
        ->assertSuccessful()
        ->assertJsonPath('data.block.type', 'block-link')
        ->assertJsonPath('data.block.active', true)
        ->assertJsonPath('data.editor.type', 'block-link');

    $firstId = (int) $create->json('data.block.id');

    $second = $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'block-link'])
        ->assertSuccessful();

    $secondId = (int) $second->json('data.block.id');

    setCurrentTenant($tenant);
    $existingOrder = Block::queryForTenantRoots()
        ->userBlocks()
        ->orderBy('sort_order')
        ->pluck('id')
        ->all();

    $reordered = collect($existingOrder)
        ->reject(fn (int $id): bool => in_array($id, [$firstId, $secondId], true))
        ->values()
        ->all();

    array_unshift($reordered, $secondId, $firstId);

    $response = $this->actingAs($user)
        ->putJson('/api/page/blocks/reorder', [
            'order' => $reordered,
        ])
        ->assertSuccessful();

    expect($response->json('data.user_blocks.0.id'))->toBe($secondId);
    expect($response->json('data.user_blocks.1.id'))->toBe($firstId);

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$firstId.'/active', ['active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false);

    $this->actingAs($user)
        ->deleteJson('/api/page/blocks/'.$secondId)
        ->assertSuccessful()
        ->assertJsonStructure(['message']);

    setCurrentTenant($tenant);
    expect(Block::queryForTenantRoots()->userBlocks()->whereKey($secondId)->exists())->toBeFalse();
    expect(Block::queryForTenantRoots()->userBlocks()->whereKey($firstId)->value('active'))->toBeFalse();
});

test('cannot create default system block types', function () {
    [$user] = createUserWithTenantForPageStructure();

    $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'header'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

test('cannot delete system blocks', function () {
    [$user, $tenant] = createUserWithTenantForPageStructure();

    setCurrentTenant($tenant);
    $header = Block::findSingleton('header');

    expect($header)->not->toBeNull();

    $this->actingAs($user)
        ->deleteJson('/api/page/blocks/'.$header->id)
        ->assertNotFound();
});

test('block-link section blocks expose content manage url and brand mark', function () {
    [$user, $tenant] = createUserWithTenantForPageStructure();

    setCurrentTenant($tenant);

    $block = Block::query()->create([
        'tenant_id' => $tenant->id,
        'component' => 'tenant::components.block-link',
        'type' => 'block-link',
        'title' => 'قسم المدونة',
        'sort_order' => 99,
        'is_default' => false,
        'status' => 'draft',
        'active' => true,
        'position' => 'home',
        'data' => [
            'link_type' => 'section',
            'content_type' => 'blog',
            'brand_mark' => [
                'type' => 'icon',
                'value' => 'tabler:book',
                'color' => '#ea580c',
            ],
        ],
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/page/structure')
        ->assertSuccessful();

    $userBlock = collect($response->json('data.user_blocks'))->firstWhere('id', $block->id);

    expect($userBlock)->not->toBeNull()
        ->and($userBlock['content_manage_url'])->toBe('/dashboard/manage/blog')
        ->and($userBlock['content_manage_label'])->toBe('إدارة المدونة')
        ->and($userBlock['icon'])->toBe('assets/icons/stationery/002-book.svg')
        ->and($userBlock['icon_url'])->toBe(asset('assets/icons/stationery/002-book.svg'))
        ->and($userBlock['brand_mark'])->toMatchArray([
            'type' => 'icon',
            'value' => 'tabler:book',
            'color' => '#ea580c',
        ]);
});

test('block-link external blocks keep the block-type icon', function () {
    [$user, $tenant] = createUserWithTenantForPageStructure();

    setCurrentTenant($tenant);

    $blockTypeIcon = app(BlockTypeRegistry::class)->iconPaths()['block-link']
        ?? 'assets/icons/tabler/Blockquote.svg';

    $block = Block::query()->create([
        'tenant_id' => $tenant->id,
        'component' => 'tenant::components.block-link',
        'type' => 'block-link',
        'title' => 'رابط خارجي',
        'sort_order' => 99,
        'is_default' => false,
        'status' => 'draft',
        'active' => true,
        'position' => 'home',
        'data' => [
            'link_type' => 'external',
            'url' => 'https://example.com',
            'title' => 'رابط خارجي',
        ],
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/page/structure')
        ->assertSuccessful()
        ->assertJsonFragment([
            'id' => $block->id,
            'icon' => $blockTypeIcon,
            'icon_url' => asset($blockTypeIcon),
        ]);

    $userBlock = collect($response->json('data.user_blocks'))->firstWhere('id', $block->id);

    expect($userBlock['brand_mark'] ?? null)->toBeNull();
});

test('users without a tenant cannot access page structure', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->getJson('/api/page/structure')
        ->assertForbidden();
});

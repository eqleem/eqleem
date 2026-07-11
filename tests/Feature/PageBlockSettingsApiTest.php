<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'show_scroll_top' => false,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.position', 'bottom-start')
        ->assertJsonPath('data.editor.show_scroll_top', false);
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

test('owner can update block-link settings', function () {
    [$user, $tenant] = createUserWithTenantForPageBlockSettings();

    $create = $this->actingAs($user)
        ->postJson('/api/page/blocks', ['type' => 'block-link'])
        ->assertSuccessful();

    $blockId = (int) $create->json('data.id');

    $this->actingAs($user)
        ->putJson('/api/page/blocks/'.$blockId, [
            'link_type' => 'section:blog',
            'title' => 'المدونة',
            'description' => 'اقرأ مقالاتنا',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.title', 'المدونة')
        ->assertJsonPath('data.block.title', 'المدونة');
});

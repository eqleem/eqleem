<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Block;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant, 2: Block}
 */
function createHeaderBrandMarkContext(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر الشعار',
        'handle' => 'logo-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    $block = Block::findSingleton('header');

    return [$user->fresh(), $tenant->fresh(), $block];
}

test('owner can search tabler icons with pagination', function () {
    [$user] = createHeaderBrandMarkContext();

    $this->actingAs($user)
        ->getJson('/api/icons/tabler?q=home&per_page=12')
        ->assertSuccessful()
        ->assertJsonPath('meta.per_page', 12)
        ->assertJsonStructure([
            'data' => [
                ['id', 'name'],
            ],
            'meta' => ['page', 'per_page', 'total', 'has_more'],
        ]);
});

test('owner can save an emoji brand mark on the header block', function () {
    [$user, $tenant, $block] = createHeaderBrandMarkContext();

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id, [
            'name' => 'صفحة بالإيموجي',
            'bio' => 'نبذة',
            'country' => 'SA',
            'city' => 'الرياض',
            'brand_mark_type' => 'emoji',
            'brand_mark_value' => '🚀',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.brand_mark.type', 'emoji')
        ->assertJsonPath('data.editor.brand_mark.value', '🚀');

    expect(data_get($tenant->fresh()->meta, 'brand_mark.type'))->toBe('emoji')
        ->and(data_get($tenant->fresh()->meta, 'brand_mark.value'))->toBe('🚀');
});

test('owner can save an icon brand mark with color on the header block', function () {
    [$user, $tenant, $block] = createHeaderBrandMarkContext();

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id, [
            'name' => 'صفحة بالأيقونة',
            'bio' => 'نبذة',
            'country' => 'SA',
            'city' => 'جدة',
            'brand_mark_type' => 'icon',
            'brand_mark_value' => 'tabler:chart-line',
            'brand_mark_color' => '#DC2626',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.brand_mark.type', 'icon')
        ->assertJsonPath('data.editor.brand_mark.value', 'tabler:chart-line')
        ->assertJsonPath('data.editor.brand_mark.color', '#dc2626');

    expect(data_get($tenant->fresh()->meta, 'brand_mark.type'))->toBe('icon')
        ->and(data_get($tenant->fresh()->meta, 'brand_mark.value'))->toBe('tabler:chart-line');
});

test('owner can still upload an image logo on the header block', function () {
    Storage::fake('spaces');

    [$user, $tenant, $block] = createHeaderBrandMarkContext();

    $this->actingAs($user)
        ->post('/api/page/blocks/'.$block->id, [
            'name' => 'صفحة بالصورة',
            'bio' => 'نبذة',
            'country' => 'SA',
            'city' => 'الدمام',
            'brand_mark_type' => 'image',
            'logo' => UploadedFile::fake()->image('logo.jpg', 200, 200),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.editor.brand_mark.type', 'image');

    expect(app(TenantProfileService::class)->hasLogo($tenant->fresh()))->toBeTrue()
        ->and(data_get($tenant->fresh()->meta, 'brand_mark.type'))->toBe('image');
});

test('owner can remove the brand mark from the header block', function () {
    [$user, $tenant, $block] = createHeaderBrandMarkContext();

    app(TenantProfileService::class)->saveBrandMark($tenant, [
        'type' => 'emoji',
        'value' => '🔥',
    ]);

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id, [
            'name' => 'بدون شعار',
            'bio' => '',
            'country' => 'SA',
            'city' => '',
            'brand_mark_type' => 'none',
            'remove_logo' => true,
        ])
        ->assertSuccessful();

    expect(app(TenantProfileService::class)->hasLogo($tenant->fresh()))->toBeFalse();
});

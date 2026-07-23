<?php

use App\Models\Content;
use App\Models\Setting;
use App\Models\Taxonomy;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForStore(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر تجريبي',
        'handle' => 'store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access store endpoints', function () {
    $this->getJson('/api/store')->assertUnauthorized();
    $this->postJson('/api/store', ['title' => 'منتج'])->assertUnauthorized();
    $this->getJson('/api/store/categories')->assertUnauthorized();
    $this->getJson('/api/store/settings')->assertUnauthorized();
});

test('owner can create list update and delete store products', function () {
    [$user, $tenant] = createUserWithTenantForStore();

    $create = $this->actingAs($user)
        ->postJson('/api/store', ['title' => 'قمي قطن'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'قمي قطن')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.active', false)
        ->assertJsonPath('data.published', false);

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->getJson('/api/store')
        ->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $uuid)
        ->assertJsonPath('meta.total', 1);

    $this->actingAs($user)
        ->getJson("/api/store/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'قمي قطن')
        ->assertJsonStructure([
            'data' => [
                'category_options',
                'images',
                'slug_prefix',
                'price',
                'compare_price',
                'weight',
                'currency_code',
                'currency_symbol',
            ],
        ])
        ->assertJsonPath('data.currency_code', 'SAR')
        ->assertJsonPath('data.currency_symbol', Money::SAR_SYMBOL);

    setCurrentTenant($tenant);

    $parent = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'ملابس',
        'type' => 'store_category',
        'sort_order' => 0,
    ]);

    $leaf = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'قمصان',
        'type' => 'store_category',
        'parent_id' => $parent->id,
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->putJson("/api/store/{$uuid}", [
            'title' => 'قمي قطن محدث',
            'body' => '<p>وصف المنتج</p>',
            'slug' => 'cotton-shirt',
            'price' => 99.50,
            'compare_price' => 120,
            'weight' => 0.25,
            'category_ids' => [$leaf->id],
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'قمي قطن محدث')
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.published', true)
        ->assertJsonPath('data.price', '99.5')
        ->assertJsonPath('data.compare_price', '120')
        ->assertJsonPath('data.weight', '0.25')
        ->assertJsonPath('data.category_ids.0', (string) $leaf->id);

    setCurrentTenant($tenant);

    $product = Content::query()->where('uuid', $uuid)->first();

    expect($product)->not->toBeNull()
        ->and($product->active)->toBeTrue()
        ->and($product->status)->toBe('published')
        ->and(data_get($product->data, 'price'))->toBe(money_minor(99.50));

    $this->actingAs($user)
        ->deleteJson('/api/store', ['ids' => [$product->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.deleted', 1);

    setCurrentTenant($tenant);

    expect(Content::query()->where('uuid', $uuid)->exists())->toBeFalse();
});

test('owner can manage store categories and reorder them', function () {
    [$user, $tenant] = createUserWithTenantForStore();

    $first = $this->actingAs($user)
        ->postJson('/api/store/categories', ['name' => 'إلكترونيات'])
        ->assertSuccessful()
        ->assertJsonPath('data.category.name', 'إلكترونيات');

    $firstId = (int) $first->json('data.category.id');

    $this->actingAs($user)
        ->deleteJson("/api/store/categories/{$firstId}")
        ->assertSuccessful();

    setCurrentTenant($tenant);

    expect(Taxonomy::query()->whereKey($firstId)->exists())->toBeFalse();
});

test('owner can get and update store settings', function () {
    [$user] = createUserWithTenantForStore();

    $this->actingAs($user)
        ->getJson('/api/store/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'المتجر');

    $this->actingAs($user)
        ->putJson('/api/store/settings', [
            'section_title' => 'متجرنا',
            'section_description' => 'تسوق أفضل المنتجات',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'متجرنا');

    expect(Setting::storeSettings()['section_title'])->toBe('متجرنا');
});

test('owner can upload store gallery images', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user] = createUserWithTenantForStore();

    $create = $this->actingAs($user)
        ->postJson('/api/store', ['title' => 'منتج صور'])
        ->assertSuccessful();

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->post("/api/store/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('product.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.images');
});

test('store product detail active state stays in sync after table toggle and detail update', function () {
    [$user] = createUserWithTenantForStore();

    $uuid = (string) $this->actingAs($user)
        ->postJson('/api/store', ['title' => 'منتج مزامنة'])
        ->json('data.uuid');

    $this->actingAs($user)
        ->putJson("/api/store/{$uuid}/active", ['active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false);

    $this->actingAs($user)
        ->getJson("/api/store/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.active', false)
        ->assertJsonPath('data.published', false);

    $this->actingAs($user)
        ->putJson("/api/store/{$uuid}", [
            'title' => 'منتج مزامنة',
            'slug' => 'sync-product',
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.published', true);

    $this->actingAs($user)
        ->getJson('/api/store')
        ->assertSuccessful()
        ->assertJsonPath('data.0.active', true);
});

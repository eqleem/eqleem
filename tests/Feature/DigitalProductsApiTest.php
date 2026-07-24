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
function createUserWithTenantForDigitalProducts(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'منتجات رقمية',
        'handle' => 'digital-products-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access digital products endpoints', function () {
    $this->getJson('/api/digital-products')->assertUnauthorized();
    $this->postJson('/api/digital-products', ['title' => 'منتج'])->assertUnauthorized();
    $this->getJson('/api/digital-products/categories')->assertUnauthorized();
    $this->getJson('/api/digital-products/settings')->assertUnauthorized();
});

test('owner can create list update and delete digital products', function () {
    [$user, $tenant] = createUserWithTenantForDigitalProducts();

    $create = $this->actingAs($user)
        ->postJson('/api/digital-products', ['title' => 'دليل PDF'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'دليل PDF')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.active', false)
        ->assertJsonPath('data.published', false);

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->getJson('/api/digital-products')
        ->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $uuid)
        ->assertJsonPath('meta.total', 1);

    $this->actingAs($user)
        ->getJson("/api/digital-products/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'دليل PDF')
        ->assertJsonStructure([
            'data' => [
                'category_options',
                'images',
                'downloads',
                'slug_prefix',
                'price',
                'compare_price',
                'currency_symbol',
                'active',
                'subtitle',
            ],
        ]);

    setCurrentTenant($tenant);

    $leaf = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'أدلة',
        'type' => Content::TAXONOMY_DIGITAL_STORE,
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->putJson("/api/digital-products/{$uuid}", [
            'title' => 'دليل PDF محدث',
            'subtitle' => 'دليل شامل',
            'body' => '<p>محتوى المنتج</p>',
            'slug' => 'pdf-guide',
            'price' => 49.99,
            'compare_price' => 79,
            'category_ids' => [$leaf->id],
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'دليل PDF محدث')
        ->assertJsonPath('data.subtitle', 'دليل شامل')
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.published', true)
        ->assertJsonPath('data.price', '49.99')
        ->assertJsonPath('data.compare_price', '79')
        ->assertJsonPath('data.category_ids.0', (string) $leaf->id);

    setCurrentTenant($tenant);

    $product = Content::query()->where('uuid', $uuid)->first();

    expect($product)->not->toBeNull()
        ->and($product->active)->toBeTrue()
        ->and($product->status)->toBe('published')
        ->and(data_get($product->data, 'price'))->toBe(money_minor(49.99));

    $this->actingAs($user)
        ->deleteJson('/api/digital-products', ['ids' => [$product->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.deleted', 1);

    setCurrentTenant($tenant);

    expect(Content::query()->where('uuid', $uuid)->exists())->toBeFalse();
});

test('owner can manage digital product categories', function () {
    [$user, $tenant] = createUserWithTenantForDigitalProducts();

    $first = $this->actingAs($user)
        ->postJson('/api/digital-products/categories', ['name' => 'قوالب'])
        ->assertSuccessful()
        ->assertJsonPath('data.category.name', 'قوالب');

    $firstId = (int) $first->json('data.category.id');

    $this->actingAs($user)
        ->deleteJson("/api/digital-products/categories/{$firstId}")
        ->assertSuccessful();

    setCurrentTenant($tenant);

    expect(Taxonomy::query()->whereKey($firstId)->exists())->toBeFalse();
});

test('owner can get and update digital product settings', function () {
    [$user] = createUserWithTenantForDigitalProducts();

    $this->actingAs($user)
        ->getJson('/api/digital-products/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'المنتجات الرقمية');

    $this->actingAs($user)
        ->putJson('/api/digital-products/settings', [
            'section_title' => 'متجرنا الرقمي',
            'section_description' => 'أفضل المنتجات الرقمية',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'متجرنا الرقمي');

    expect(Setting::digitalProductSettings()['section_title'])->toBe('متجرنا الرقمي');
});

test('owner can upload gallery images and download files', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user] = createUserWithTenantForDigitalProducts();

    $create = $this->actingAs($user)
        ->postJson('/api/digital-products', ['title' => 'منتج ملفات'])
        ->assertSuccessful();

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->post("/api/digital-products/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('product.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.images');

    $this->actingAs($user)
        ->post("/api/digital-products/{$uuid}/downloads", [
            'file' => UploadedFile::fake()->create('guide.pdf', 100, 'application/pdf'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.downloads');
});

test('owner can reorder and delete gallery images and downloads', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user] = createUserWithTenantForDigitalProducts();

    $create = $this->actingAs($user)
        ->postJson('/api/digital-products', ['title' => 'منتج ترتيب'])
        ->assertSuccessful();

    $uuid = (string) $create->json('data.uuid');

    $firstImage = $this->actingAs($user)
        ->post("/api/digital-products/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('first.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $secondImage = $this->actingAs($user)
        ->post("/api/digital-products/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('second.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $imageIdA = (int) $firstImage->json('data.images.0.id');
    $imageIdB = (int) collect($secondImage->json('data.images'))->pluck('id')->first(fn (int $id): bool => $id !== $imageIdA);

    $this->actingAs($user)
        ->putJson("/api/digital-products/{$uuid}/images/reorder", [
            'order' => [$imageIdB, $imageIdA],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.images.0.id', $imageIdB)
        ->assertJsonPath('data.images.1.id', $imageIdA);

    $this->actingAs($user)
        ->deleteJson("/api/digital-products/{$uuid}/images/{$imageIdA}")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.images')
        ->assertJsonPath('data.images.0.id', $imageIdB);

    $firstDownload = $this->actingAs($user)
        ->post("/api/digital-products/{$uuid}/downloads", [
            'file' => UploadedFile::fake()->create('a.pdf', 50, 'application/pdf'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $secondDownload = $this->actingAs($user)
        ->post("/api/digital-products/{$uuid}/downloads", [
            'file' => UploadedFile::fake()->create('b.pdf', 50, 'application/pdf'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $downloadIdA = (int) $firstDownload->json('data.downloads.0.id');
    $downloadIdB = (int) collect($secondDownload->json('data.downloads'))->pluck('id')->first(fn (int $id): bool => $id !== $downloadIdA);

    $this->actingAs($user)
        ->putJson("/api/digital-products/{$uuid}/downloads/reorder", [
            'order' => [$downloadIdB, $downloadIdA],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.downloads.0.id', $downloadIdB)
        ->assertJsonPath('data.downloads.1.id', $downloadIdA);

    $this->actingAs($user)
        ->deleteJson("/api/digital-products/{$uuid}/downloads/{$downloadIdA}")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.downloads')
        ->assertJsonPath('data.downloads.0.id', $downloadIdB);
});

test('owner can reorder digital product categories', function () {
    [$user, $tenant] = createUserWithTenantForDigitalProducts();

    $first = $this->actingAs($user)
        ->postJson('/api/digital-products/categories', ['name' => 'أول'])
        ->assertSuccessful();

    $second = $this->actingAs($user)
        ->postJson('/api/digital-products/categories', ['name' => 'ثاني'])
        ->assertSuccessful();

    $firstId = (int) $first->json('data.category.id');
    $secondId = (int) $second->json('data.category.id');

    $this->actingAs($user)
        ->putJson('/api/digital-products/categories/reorder', [
            'order' => [$secondId, $firstId],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.categories.0.id', $secondId)
        ->assertJsonPath('data.categories.1.id', $firstId);

    setCurrentTenant($tenant);

    expect(Taxonomy::query()->whereKey($secondId)->value('sort_order'))->toBe(0)
        ->and(Taxonomy::query()->whereKey($firstId)->value('sort_order'))->toBe(1);
});

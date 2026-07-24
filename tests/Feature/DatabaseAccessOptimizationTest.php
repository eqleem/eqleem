<?php

use App\API\Blog\ShowBlogPost;
use App\API\DigitalProducts\ListDigitalProducts;
use App\API\Store\ListStoreCategories;
use App\API\Store\ListStoreProducts;
use App\API\Store\ShowStoreProduct;
use App\API\Store\UpdateStoreProduct;
use App\Models\Content;
use App\Models\Taxonomy;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createOptimizationTenant(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Query Opt Tenant',
        'handle' => 'query-opt-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

/**
 * @param  callable(): mixed  $callback
 * @return array{0: mixed, 1: int}
 */
function withQueryCount(callable $callback): array
{
    $count = 0;

    DB::listen(function () use (&$count): void {
        $count++;
    });

    $result = $callback();

    return [$result, $count];
}

test('cart service reuses a single items query for subtotal and shipping checks', function () {
    [, $tenant] = createOptimizationTenant();

    $product = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج',
        'slug' => 'product-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 1500],
    ]);

    $cart = app(CartService::class);
    $cart->addProduct($product, 2);

    [, $queries] = withQueryCount(function () use ($cart): void {
        $items = $cart->items();
        $subtotal = $cart->subtotal();
        $requiresShipping = $cart->requiresShipping();
        $fee = $cart->shippingFee('none');

        expect($items)->toHaveCount(1)
            ->and($subtotal)->toBe(3000)
            ->and($requiresShipping)->toBeTrue()
            ->and($fee)->toBe(0);
    });

    expect($queries)->toBeLessThanOrEqual(2);
});

test('taxonomiesOfType uses eager-loaded taxonomies without extra queries', function () {
    [, $tenant] = createOptimizationTenant();

    $product = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج مصنف',
        'slug' => 'product-cat-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => [],
    ]);

    $category = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'تصنيف',
        'type' => 'store_category',
        'slug' => 'cat-'.Str::lower(Str::random(4)),
    ]);

    $product->attachTaxonomies([$category->id]);

    $loaded = Content::query()
        ->whereKey($product->id)
        ->with('taxonomies')
        ->firstOrFail();

    [, $queries] = withQueryCount(function () use ($loaded, $category): void {
        $ids = $loaded->taxonomiesOfType('store_category')->pluck('id')->all();

        expect($ids)->toContain($category->id);
    });

    expect($queries)->toBe(0);
});

test('show store product loads media and taxonomies without a second content select', function () {
    [$user, $tenant] = createOptimizationTenant();

    $product = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج عرض',
        'slug' => 'show-'.Str::lower(Str::random(4)),
        'status' => 'draft',
        'active' => false,
        'data' => ['price' => 100],
    ]);

    $sql = [];

    DB::listen(function ($query) use (&$sql): void {
        $sql[] = $query->sql;
    });

    $content = ShowStoreProduct::run($tenant, $product->uuid);

    expect($content->relationLoaded('media'))->toBeTrue()
        ->and($content->relationLoaded('taxonomies'))->toBeTrue();

    $contentSelects = collect($sql)->filter(
        fn (string $statement): bool => str_contains(strtolower($statement), 'from `contents`')
            || str_contains(strtolower($statement), 'from "contents"'),
    );

    expect($contentSelects)->toHaveCount(1);

    $this->actingAs($user)
        ->getJson("/api/store/{$product->uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.uuid', $product->uuid);
});

test('digital products list exposes downloads_count without loading download media rows', function () {
    [$user, $tenant] = createOptimizationTenant();

    Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('digital-products'),
        'title' => 'ملف رقمي',
        'slug' => 'digital-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 500],
    ]);

    $page = ListDigitalProducts::run($tenant);

    expect($page->first()->downloads_count)->toBe(0)
        ->and($page->first()->relationLoaded('media'))->toBeTrue();

    $this->actingAs($user)
        ->getJson('/api/digital-products')
        ->assertSuccessful()
        ->assertJsonPath('data.0.downloads_count', 0);
});

test('list store categories returns tree and parent options from a shared category tree', function () {
    [, $tenant] = createOptimizationTenant();

    Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'أب',
        'type' => 'store_category',
        'slug' => 'parent-'.Str::lower(Str::random(4)),
    ]);

    $payload = ListStoreCategories::run($tenant);

    expect($payload['categories'])->not->toBeEmpty()
        ->and($payload['parent_options'])->not->toBeEmpty()
        ->and($payload['parent_options'][0])->toMatchArray(['id' => '', 'label' => 'بدون تصنيف أب']);
});

test('store products list constrains eager-loaded media to the store-media collection', function () {
    [, $tenant] = createOptimizationTenant();

    Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج قائمة',
        'slug' => 'list-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 100],
    ]);

    $sql = [];

    DB::listen(function ($query) use (&$sql): void {
        $sql[] = $query->sql;
    });

    $page = ListStoreProducts::run($tenant);

    expect($page)->toHaveCount(1)
        ->and($page->first()->relationLoaded('media'))->toBeTrue();

    $mediaQueries = collect($sql)->filter(
        fn (string $statement): bool => str_contains(strtolower($statement), 'collection_name'),
    );

    expect($mediaQueries)->not->toBeEmpty();
});

test('update store product refreshes media and taxonomies together', function () {
    [, $tenant] = createOptimizationTenant();

    $product = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج تحديث',
        'slug' => 'update-'.Str::lower(Str::random(4)),
        'status' => 'draft',
        'active' => false,
        'data' => ['price' => 100, 'body' => '', 'editor_mode' => 'html'],
    ]);

    $category = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'تصنيف تحديث',
        'type' => 'store_category',
        'slug' => 'upd-cat-'.Str::lower(Str::random(4)),
    ]);

    $updated = UpdateStoreProduct::run($tenant, $product->uuid, [
        'title' => 'منتج محدث',
        'slug' => $product->slug,
        'body' => '',
        'editor_mode' => 'html',
        'price' => 2,
        'compare_price' => null,
        'weight' => null,
        'category_ids' => [$category->id],
        'active' => true,
    ]);

    expect($updated->relationLoaded('media'))->toBeTrue()
        ->and($updated->relationLoaded('taxonomies'))->toBeTrue()
        ->and($updated->taxonomiesOfType('store_category')->pluck('id')->all())->toContain($category->id);
});

test('show blog post loads taxonomies without a second content select', function () {
    [, $tenant] = createOptimizationTenant();

    $post = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('blog'),
        'title' => 'مقال',
        'slug' => 'blog-'.Str::lower(Str::random(4)),
        'status' => 'draft',
        'active' => false,
        'data' => [],
    ]);

    $sql = [];

    DB::listen(function ($query) use (&$sql): void {
        $sql[] = $query->sql;
    });

    $content = ShowBlogPost::run($tenant, $post->uuid);

    expect($content->relationLoaded('taxonomies'))->toBeTrue()
        ->and($content->relationLoaded('media'))->toBeTrue();

    $contentSelects = collect($sql)->filter(
        fn (string $statement): bool => str_contains(strtolower($statement), 'from `contents`')
            || str_contains(strtolower($statement), 'from "contents"'),
    );

    expect($contentSelects)->toHaveCount(1);
});

test('course lesson count avoids intermediate collections', function () {
    $content = new Content([
        'data' => [
            'chapters' => [
                ['lessons' => [['id' => 1], ['id' => 2]]],
                ['lessons' => [['id' => 3]]],
                'invalid',
            ],
        ],
    ]);

    expect($content->courseLessonCount())->toBe(3);
});

<?php

use App\Models\Client;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createOrderApiUser(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'create-order-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'create-order-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

function createOrderApiClient(Tenant $tenant, array $overrides = []): Client
{
    setCurrentTenant($tenant);

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'محمد العتيبي',
        'email' => 'client-'.Str::lower(Str::random(6)).'@example.com',
        'phone' => '05'.random_int(10000000, 99999999),
        'tenant_id' => $tenant->id,
        'active' => true,
        ...$overrides,
    ]);

    $client->tenants()->attach($tenant->id, [
        'active' => true,
        'meta' => [
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
        ],
    ]);

    return $client;
}

test('guests cannot create orders', function () {
    $this->postJson('/api/orders', [])
        ->assertUnauthorized();
});

test('owner can create a draft order with product item and client', function () {
    [$user, $tenant] = createOrderApiUser();
    $client = createOrderApiClient($tenant);

    setCurrentTenant($tenant);

    $product = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج تجريبي',
        'slug' => 'test-product-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'active' => true,
        'price' => 5000,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/orders', [
            'client_id' => $client->id,
            'items' => [
                [
                    'type' => 'product',
                    'name' => $product->title,
                    'product_id' => $product->id,
                    'qty' => 2,
                    'unit_price' => 50,
                    'discount' => 10,
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.client.name', $client->name)
        ->assertJsonPath('data.status', 'new')
        ->assertJsonPath('data.payment_status', 'unpaid')
        ->assertJsonPath('data.items.0.name', 'منتج تجريبي')
        ->assertJsonPath('data.items.0.qty', 2);

    $order = Order::query()->where('uuid', $response->json('data.uuid'))->first();

    expect($order)->not->toBeNull()
        ->and((int) $order->subtotal)->toBe(10000)
        ->and((int) $order->discount_total)->toBe(1000)
        ->and((int) $order->grand_total)->toBe(9000)
        ->and((int) $order->client_id)->toBe($client->id);

    $item = DB::table('order_items')->where('order_id', $order->id)->first();

    expect($item)->not->toBeNull()
        ->and((int) $item->product_id)->toBe($product->id)
        ->and((int) $item->unit_price)->toBe(5000)
        ->and((int) $item->discount_total)->toBe(1000)
        ->and((int) $item->line_total)->toBe(9000)
        ->and(json_decode($item->meta, true)['type'])->toBe('product');
});

test('owner can create walking-client order with custom other item', function () {
    [$user] = createOrderApiUser();

    $this->actingAs($user)
        ->postJson('/api/orders', [
            'client_id' => null,
            'items' => [
                [
                    'type' => 'other',
                    'name' => 'خدمة مخصصة',
                    'qty' => 1,
                    'unit_price' => 25.5,
                    'discount' => 0,
                    'description' => 'خدمة مخصصة',
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.client', null)
        ->assertJsonPath('data.items.0.name', 'خدمة مخصصة');

    $order = Order::query()->latest('id')->first();

    expect($order->client_id)->toBeNull()
        ->and((int) $order->grand_total)->toBe(2550);
});

test('content search returns matching products for order type', function () {
    [$user, $tenant] = createOrderApiUser();
    setCurrentTenant($tenant);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'Arabic Coffee',
        'slug' => 'arabic-coffee-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'active' => true,
        'price' => 1500,
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/orders/content-search?'.http_build_query([
            'type' => 'product',
            'search' => 'Coffee',
        ]));

    $response->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Arabic Coffee')
        ->assertJsonPath('data.0.unit_price', 15);
});

test('content search returns data price for services and unit rentals', function (string $orderType, string $contentTypeKey) {
    [$user, $tenant] = createOrderApiUser();
    setCurrentTenant($tenant);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel($contentTypeKey),
        'title' => 'Priced Booking Item',
        'slug' => 'priced-booking-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'active' => true,
        'price' => 0,
        'data' => [
            'price' => 12500,
            'duration_minutes' => 45,
        ],
    ]);

    $this->actingAs($user)
        ->getJson('/api/orders/content-search?'.http_build_query([
            'type' => $orderType,
            'search' => 'Priced',
        ]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Priced Booking Item')
        ->assertJsonPath('data.0.unit_price', 125)
        ->assertJsonPath('data.0.duration_minutes', 45);
})->with([
    'service' => ['service', 'services'],
    'unit_rental' => ['unit_rental', 'unit-rental'],
]);

test('foreign client id is rejected when creating order', function () {
    [$user] = createOrderApiUser();
    [, $otherTenant] = createOrderApiUser();
    $foreignClient = createOrderApiClient($otherTenant);

    $this->actingAs($user)
        ->postJson('/api/orders', [
            'client_id' => $foreignClient->id,
            'items' => [
                [
                    'type' => 'other',
                    'name' => 'عنصر',
                    'qty' => 1,
                    'unit_price' => 10,
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['client_id']);
});

test('create order requires at least one item', function () {
    [$user] = createOrderApiUser();

    $this->actingAs($user)
        ->postJson('/api/orders', [
            'items' => [],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['items']);
});

test('owner can create draft content for available order item types', function () {
    [$user, $tenant] = createOrderApiUser();

    $response = $this->actingAs($user)
        ->postJson('/api/orders/content', [
            'type' => 'product',
            'title' => 'منتج مسودة جديد',
            'unit_price' => 42.5,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'منتج مسودة جديد')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.unit_price', 42.5);

    $productId = $response->json('data.product_id');

    setCurrentTenant($tenant);

    $content = Content::query()->find($productId);

    expect($content)->not->toBeNull()
        ->and($content->status)->toBe('draft')
        ->and($content->title)->toBe('منتج مسودة جديد')
        ->and((int) $content->price)->toBe(4250);

    $this->actingAs($user)
        ->postJson('/api/orders', [
            'items' => [
                [
                    'type' => 'product',
                    'name' => 'منتج مسودة جديد',
                    'product_id' => $productId,
                    'qty' => 1,
                    'unit_price' => 42.5,
                    'discount' => 0,
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.items.0.name', 'منتج مسودة جديد');
});

test('updating draft content price syncs contents.price', function () {
    [$user, $tenant] = createOrderApiUser();

    $this->actingAs($user)
        ->postJson('/api/orders/content', [
            'type' => 'product',
            'title' => 'منتج بدون سعر',
            'unit_price' => 0,
        ])
        ->assertSuccessful();

    $this->actingAs($user)
        ->postJson('/api/orders/content', [
            'type' => 'product',
            'title' => 'منتج بدون سعر',
            'unit_price' => 75,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.unit_price', 75)
        ->assertJsonPath('data.status', 'draft');

    setCurrentTenant($tenant);

    $content = Content::query()->where('title', 'منتج بدون سعر')->first();

    expect($content)->not->toBeNull()
        ->and((int) $content->price)->toBe(7500);
});

test('other item type cannot be created as system content', function () {
    [$user] = createOrderApiUser();

    $this->actingAs($user)
        ->postJson('/api/orders/content', [
            'type' => 'other',
            'title' => 'عنصر مخصص',
            'unit_price' => 10,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

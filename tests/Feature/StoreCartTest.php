<?php

use App\Livewire\Tenant\Pages\Checkout;
use App\Livewire\Tenant\Store\Detail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Client;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CartService;
use App\Services\ClientAuthService;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);

    view()->prependNamespace('tenant-theme', public_path('themes/default'));
    view()->prependNamespace('default-tenant-theme', public_path('themes/default'));
});

/**
 * @return array{tenant: Tenant, product: Content}
 */
function createStoreCartContext(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Store Tenant',
        'handle' => 'store-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $product = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج تجريبي',
        'slug' => 'demo-product',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => [
            'price' => 25000,
            'body' => 'وصف المنتج',
        ],
    ]);

    return compact('tenant', 'product');
}

it('adds a store product to the cart', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    Livewire::test(Detail::class, ['slug' => $product->slug])
        ->call('addToCart')
        ->assertSet('addedToCart', true);

    expect(Cart::query()->count())->toBe(1)
        ->and(CartItem::query()->count())->toBe(1)
        ->and(CartItem::query()->value('quantity'))->toBe(1)
        ->and(CartItem::query()->value('unit_price'))->toBe(25000)
        ->and(CartItem::query()->value('productable_type'))->toBe(Content::class)
        ->and(CartItem::query()->value('productable_id'))->toBe($product->id);

    $item = CartItem::query()->first();

    expect($item->itemType())->toBe('product');
});

it('stores different content types using productable morph', function () {
    ['tenant' => $tenant] = createStoreCartContext();

    setCurrentTenant($tenant);

    $service = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('services'),
        'title' => 'خدمة تصوير',
        'slug' => 'photo-service',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 15000],
    ]);

    $course = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('courses'),
        'title' => 'دورة برمجة',
        'slug' => 'programming-course',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 9900],
    ]);

    $cart = app(CartService::class);
    $cart->addItem($service);
    $cart->addItem($course);

    $items = CartItem::query()->orderBy('id')->get();

    expect($items)->toHaveCount(2)
        ->and($items[0]->productable_id)->toBe($service->id)
        ->and($items[0]->itemType())->toBe('service')
        ->and($items[1]->productable_id)->toBe($course->id)
        ->and($items[1]->itemType())->toBe('course');
});

it('shows cart items and updates quantity', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    Livewire::test(Detail::class, ['slug' => $product->slug])
        ->call('addToCart');

    Livewire::test(App\Livewire\Tenant\Pages\Cart::class)
        ->assertSee('منتج تجريبي')
        ->call('updateQuantity', CartItem::query()->value('id'), 3)
        ->assertSee('3');

    expect(CartItem::query()->value('quantity'))->toBe(3);
});

it('creates an ecommerce order from checkout and clears the cart', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    Livewire::test(Detail::class, ['slug' => $product->slug])
        ->set('quantity', 2)
        ->call('addToCart');

    Livewire::test(Checkout::class)
        ->set('name', 'أحمد محمد')
        ->set('phone', '0500000000')
        ->set('email', 'ahmad@example.com')
        ->set('shippingMethod', 'pickup')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertRedirect(route('tenant.store.index', ['tenant' => $tenant->handle]));

    expect(CartItem::query()->count())->toBe(0)
        ->and(Order::query()->count())->toBe(1);

    $order = Order::query()->first();

    expect($order->channel)->toBe('ecommerce')
        ->and($order->grand_total)->toBe(50000)
        ->and(DB::table('order_items')->where('order_id', $order->id)->count())->toBe(1);

    $orderItemMeta = json_decode((string) DB::table('order_items')->where('order_id', $order->id)->value('meta'), true);

    expect($orderItemMeta['type'] ?? null)->toBe('product');
});

it('resolves cart totals through the cart service', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    app(CartService::class)->addProduct($product, 2);

    $cart = app(CartService::class);

    expect($cart->itemCount())->toBe(2)
        ->and($cart->subtotal())->toBe(50000)
        ->and($cart->grandTotal('express'))->toBe(53500)
        ->and($cart->grandTotal('pickup'))->toBe(50000);
});

it('merges guest cart into client cart after login', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    $guestSessionId = session()->getId();

    app(CartService::class)->addProduct($product, 2);

    expect(Cart::query()->whereNull('client_id')->count())->toBe(1)
        ->and(CartItem::query()->sum('quantity'))->toBe(2);

    $client = Client::withoutGlobalScope('tenantable')->create([
        'name' => 'عميل مسجل',
        'email' => 'guest-merge@example.com',
        'phone' => '0511111111',
        'tenant_id' => $tenant->id,
    ]);

    $client->tenants()->attach($tenant->id, [
        'active' => true,
        'meta' => [
            'name' => 'عميل مسجل',
            'email' => 'guest-merge@example.com',
            'phone' => '0511111111',
        ],
    ]);

    app(CartService::class)->stashGuestCartReference($tenant->id);

    session()->regenerate();

    $this->actingAs($client, 'client');

    app(CartService::class)->mergeGuestCartInto($client, $tenant->id, $guestSessionId);

    expect(Cart::query()->whereNull('client_id')->count())->toBe(0)
        ->and(Cart::query()->where('client_id', $client->id)->count())->toBe(1)
        ->and(CartItem::query()->sum('quantity'))->toBe(2);

    expect(app(CartService::class)->itemCount())->toBe(2);
});

it('merges guest cart through client authentication flow', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    $guestSessionId = session()->getId();

    app(CartService::class)->addProduct($product, 2);

    session()->regenerate();

    app(ClientAuthService::class)->authenticateForTenant('auth-flow@example.com', $tenant, [
        'name' => 'عميل مسجل',
        'email' => 'auth-flow@example.com',
    ]);

    expect(Cart::query()->whereNull('client_id')->count())->toBe(0)
        ->and(CartItem::query()->sum('quantity'))->toBe(2)
        ->and(app(CartService::class)->itemCount())->toBe(2)
        ->and($guestSessionId)->not->toBe(session()->getId());
});

it('merges guest cart quantities with existing client cart items', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    $client = Client::withoutGlobalScope('tenantable')->create([
        'name' => 'عميل مسجل',
        'email' => 'merge-qty@example.com',
        'phone' => '0522222222',
        'tenant_id' => $tenant->id,
    ]);

    $clientCart = Cart::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'session_id' => session()->getId(),
    ]);

    CartItem::query()->create([
        'cart_id' => $clientCart->id,
        'productable_type' => Content::class,
        'productable_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 25000,
        'meta' => ['item_type' => 'product', 'title' => $product->title],
    ]);

    app(CartService::class)->addProduct($product, 3);

    $guestSessionId = session()->getId();

    app(CartService::class)->stashGuestCartReference($tenant->id);

    session()->regenerate();

    $this->actingAs($client, 'client');

    app(CartService::class)->mergeGuestCartInto($client, $tenant->id, $guestSessionId);

    expect(CartItem::query()->sum('quantity'))->toBe(4)
        ->and(Cart::query()->count())->toBe(1);
});

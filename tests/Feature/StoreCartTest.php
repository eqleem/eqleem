<?php

use App\Livewire\Tenant\Pages\Checkout;
use App\Livewire\Tenant\Pages\OrderConfirmation;
use App\Livewire\Tenant\Store\Detail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Client;
use App\Models\Content;
use App\Models\Order;
use App\Models\Setting;
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

it('shows an empty cart state that links to the home page', function () {
    ['tenant' => $tenant] = createStoreCartContext();

    setCurrentTenant($tenant);

    Livewire::test(App\Livewire\Tenant\Pages\Cart::class)
        ->assertSee('سلتك فارغة حالياً')
        ->assertSee('تصفح المنتجات والخدمات')
        ->assertSee(route('tenant.home'), false)
        ->assertSee('hugeicons:shopping-cart-01', false);
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

    Setting::savePaymentMethod('cash-on-delivery', [
        'label' => 'الدفع عند الاستلام',
    ], true);

    $shippingMethod = enableStoreCheckoutShipping(25);

    Livewire::test(Detail::class, ['slug' => $product->slug])
        ->set('quantity', 2)
        ->call('addToCart');

    fillCheckoutShipping(Livewire::test(Checkout::class))
        ->set('name', 'أحمد محمد')
        ->set('phone', '0500000000')
        ->set('email', 'ahmad@example.com')
        ->set('shippingMethod', $shippingMethod)
        ->set('paymentMethod', 'cash-on-delivery')
        ->call('confirmCashOnDelivery');

    $order = Order::query()->firstOrFail();

    expect(CartItem::query()->count())->toBe(0)
        ->and(Order::query()->count())->toBe(1)
        ->and($order->channel)->toBe('ecommerce')
        ->and($order->grand_total)->toBe(52500)
        ->and(data_get($order->meta, 'payment_method'))->toBe('cash-on-delivery')
        ->and(DB::table('order_items')->where('order_id', $order->id)->count())->toBe(1);

    session(['recent_order_id' => $order->id]);

    Livewire::test(OrderConfirmation::class, ['order' => $order])
        ->assertSee('تم استلام طلبك بنجاح')
        ->assertSee('#'.$order->number)
        ->assertSee('منتج تجريبي')
        ->assertSee('متابعة التسوق');

    $orderItemMeta = json_decode((string) DB::table('order_items')->where('order_id', $order->id)->value('meta'), true);

    expect($orderItemMeta['type'] ?? null)->toBe('product');
});

it('resolves cart totals through the cart service', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    app(CartService::class)->addProduct($product, 2);

    $cart = app(CartService::class);
    $shippingMethod = enableStoreCheckoutShipping(25);
    $address = checkoutShippingAddress();

    expect($cart->itemCount())->toBe(2)
        ->and($cart->subtotal())->toBe(50000)
        ->and($cart->grandTotal($shippingMethod, ['country' => $address['country'], 'city_id' => $address['cityId']]))->toBe(52500)
        ->and($cart->shippingFee($shippingMethod, ['country' => $address['country'], 'city_id' => $address['cityId']]))->toBe(2500);
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

it('shows only active payment methods on checkout', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    Setting::savePaymentMethod('cash-on-delivery', ['label' => 'الدفع عند الاستلام'], true);
    Setting::savePaymentMethod('bank-transfer', [], false);

    app(CartService::class)->addProduct($product);

    Livewire::test(Checkout::class)
        ->assertSee('الدفع عند الاستلام')
        ->assertDontSee('التحويل البنكي');
});

it('requires bank transfer reference before completing checkout', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    Setting::savePaymentMethod('bank-transfer', [
        'accounts' => [[
            'id' => 'acc-1',
            'bank_name' => 'الراجحي',
            'account_name' => 'شركة اختبار',
            'iban' => 'SA1234567890123456789012',
            'account_number' => '1234567890',
        ]],
    ], true);

    app(CartService::class)->addProduct($product);

    Livewire::test(Checkout::class)
        ->set('name', 'أحمد')
        ->set('phone', '0500000000')
        ->set('paymentMethod', 'bank-transfer')
        ->set('bankTransferAccountId', 'acc-1')
        ->call('confirmBankTransfer')
        ->assertHasErrors(['bankTransferReference']);
});

it('completes a free checkout without payment methods', function () {
    ['tenant' => $tenant] = createStoreCartContext();

    setCurrentTenant($tenant);

    $course = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('courses'),
        'title' => 'دورة مجانية',
        'slug' => 'free-course',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 0],
    ]);

    app(CartService::class)->addItem($course);

    Livewire::test(Checkout::class)
        ->set('name', 'أحمد')
        ->set('phone', '0500000000')
        ->call('placeFreeOrder');

    $order = Order::query()->firstOrFail();

    expect($order->grand_total)->toBe(0)
        ->and($order->payment_status)->toBe('paid')
        ->and(data_get($order->meta, 'payment_method'))->toBe('free');

    session(['recent_order_id' => $order->id]);

    Livewire::test(OrderConfirmation::class, ['order' => $order])
        ->assertSee('تم استلام طلبك بنجاح')
        ->assertSee('#'.$order->number);
});

it('rejects free checkout when order total is greater than zero', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    $shippingMethod = enableStoreCheckoutShipping(25);

    app(CartService::class)->addProduct($product);

    fillCheckoutShipping(Livewire::test(Checkout::class))
        ->set('name', 'أحمد')
        ->set('phone', '0500000000')
        ->set('shippingMethod', $shippingMethod)
        ->call('placeFreeOrder')
        ->assertHasErrors(['paymentMethod']);
});

it('redirects to order confirmation after checkout', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    Setting::savePaymentMethod('cash-on-delivery', [], true);
    $shippingMethod = enableStoreCheckoutShipping(25);

    app(CartService::class)->addProduct($product);

    fillCheckoutShipping(Livewire::test(Checkout::class))
        ->set('name', 'أحمد')
        ->set('phone', '0500000000')
        ->set('shippingMethod', $shippingMethod)
        ->set('paymentMethod', 'cash-on-delivery')
        ->call('confirmCashOnDelivery')
        ->assertRedirect(route('tenant.pages.order-confirmation', [
            'tenant' => $tenant->handle,
            'order' => Order::query()->value('uuid'),
        ]));

    $order = Order::query()->firstOrFail();

    $this->withSession(['recent_order_id' => $order->id])
        ->get(route('tenant.pages.order-confirmation', [
            'tenant' => $tenant->handle,
            'order' => $order->uuid,
        ]))
        ->assertSuccessful()
        ->assertSee('تم استلام طلبك بنجاح');
});

it('denies viewing order confirmation without access', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    Setting::savePaymentMethod('cash-on-delivery', [], true);
    $shippingMethod = enableStoreCheckoutShipping(25);

    app(CartService::class)->addProduct($product);

    fillCheckoutShipping(Livewire::test(Checkout::class))
        ->set('name', 'أحمد')
        ->set('phone', '0500000000')
        ->set('shippingMethod', $shippingMethod)
        ->set('paymentMethod', 'cash-on-delivery')
        ->call('confirmCashOnDelivery');

    $order = Order::query()->firstOrFail();

    session()->forget('recent_order_id');

    Livewire::test(OrderConfirmation::class, ['order' => $order])
        ->assertForbidden();
});

it('stores shippable flag on cart items based on content type', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    $course = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('courses'),
        'title' => 'دورة Laravel',
        'slug' => 'laravel-course',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 8000],
    ]);

    $cart = app(CartService::class);
    $cart->addProduct($product);
    $cart->addItem($course);

    $items = CartItem::query()->orderBy('id')->get();

    expect($items[0]->isShippable())->toBeTrue()
        ->and(data_get($items[0]->meta, 'shippable'))->toBeTrue()
        ->and($items[1]->isShippable())->toBeFalse()
        ->and(data_get($items[1]->meta, 'shippable'))->toBeFalse();
});

it('shows shipping options only when the cart contains shippable items', function () {
    ['tenant' => $tenant, 'product' => $product] = createStoreCartContext();

    setCurrentTenant($tenant);

    $course = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('courses'),
        'title' => 'دورة Laravel',
        'slug' => 'laravel-course',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 8000],
    ]);

    app(CartService::class)->addItem($course);

    Livewire::test(Checkout::class)
        ->assertDontSee('عنوان الشحن');

    enableStoreCheckoutShipping();

    app(CartService::class)->addProduct($product);

    Livewire::test(Checkout::class)
        ->assertSee('عنوان الشحن')
        ->assertSee('خيارات الشحن')
        ->assertSee('شحن إقليم');
});

it('does not charge shipping for non-shippable checkout orders', function () {
    ['tenant' => $tenant] = createStoreCartContext();

    setCurrentTenant($tenant);

    $course = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('courses'),
        'title' => 'دورة Laravel',
        'slug' => 'laravel-course',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 8000],
    ]);

    Setting::savePaymentMethod('cash-on-delivery', [], true);

    app(CartService::class)->addItem($course);

    Livewire::test(Checkout::class)
        ->set('name', 'أحمد')
        ->set('phone', '0500000000')
        ->set('paymentMethod', 'cash-on-delivery')
        ->call('confirmCashOnDelivery');

    $order = Order::query()->firstOrFail();

    expect($order->grand_total)->toBe(8000)
        ->and(data_get($order->meta, 'shipping_fee'))->toBe(0)
        ->and(data_get($order->meta, 'shipping_method'))->toBe('none');
});

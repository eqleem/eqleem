<?php

use App\Livewire\Tenant\Pages\Checkout;
use App\Livewire\Tenant\Store\Detail;
use App\Mail\OrderConfirmation;
use App\Models\Client;
use App\Models\Content;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CheckoutShippingService;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
function createOrderConfirmationContext(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر التأكيد',
        'handle' => 'confirm-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $product = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج التأكيد',
        'slug' => 'confirm-product',
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

it('queues an order confirmation email after checkout', function () {
    Mail::fake();

    ['tenant' => $tenant, 'product' => $product] = createOrderConfirmationContext();

    setCurrentTenant($tenant);

    Setting::savePaymentMethod('cash-on-delivery', [], true);

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

    $order = Order::query()->first();

    Mail::assertQueued(OrderConfirmation::class, function (OrderConfirmation $mail) use ($order, $tenant) {
        return $mail->hasTo('ahmad@example.com')
            && $mail->order->is($order)
            && $mail->tenant->is($tenant)
            && $mail->customerName === 'أحمد محمد'
            && $mail->items->count() === 1;
    });
});

it('does not queue an order confirmation email when client has no email', function () {
    Mail::fake();

    ['tenant' => $tenant, 'product' => $product] = createOrderConfirmationContext();

    setCurrentTenant($tenant);

    Setting::savePaymentMethod('cash-on-delivery', [], true);

    $shippingMethod = enableStoreCheckoutShipping(25);

    Livewire::test(Detail::class, ['slug' => $product->slug])
        ->call('addToCart');

    fillCheckoutShipping(Livewire::test(Checkout::class))
        ->set('name', 'عميل بدون بريد')
        ->set('phone', '0511111111')
        ->set('email', '')
        ->set('shippingMethod', $shippingMethod)
        ->set('paymentMethod', 'cash-on-delivery')
        ->call('confirmCashOnDelivery');

    Mail::assertNotQueued(OrderConfirmation::class);
});

it('renders the order confirmation email with order details', function () {
    ['tenant' => $tenant] = createOrderConfirmationContext();

    $client = Client::withoutGlobalScope('tenantable')->create([
        'name' => 'سارة علي',
        'email' => 'sara@example.com',
        'phone' => '0522222222',
        'tenant_id' => $tenant->id,
    ]);

    $order = Order::create([
        'tenant_id' => $tenant->id,
        'type' => 'order',
        'status' => 'open',
        'channel' => 'ecommerce',
        'number' => '000001',
        'client_id' => $client->id,
        'currency_code' => 'SAR',
        'subtotal' => 50000,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => 52500,
        'paid_total' => 0,
        'due_total' => 52500,
        'payment_status' => 'unpaid',
        'issued_at' => now(),
        'financial_status' => 'open',
        'fulfillment_status' => 'unfulfilled',
        'meta' => [
            'payment_method' => 'card',
            'shipping_method' => app(CheckoutShippingService::class)->registryMethodKey('eqleem-ship'),
            'shipping_method_label' => 'شحن إقليم',
            'shipping_fee' => 2500,
            'source' => 'store_cart',
        ],
    ]);

    DB::table('order_items')->insert([
        'order_id' => $order->id,
        'name' => 'منتج التأكيد',
        'qty' => 2,
        'unit_price' => 25000,
        'discount_total' => 0,
        'tax_total' => 0,
        'line_total' => 50000,
        'meta' => json_encode(['type' => 'product']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $items = DB::table('order_items')
        ->where('order_id', $order->id)
        ->orderBy('id')
        ->get()
        ->map(function (object $item): object {
            $item->type_label = 'منتج';

            return $item;
        });

    $mail = new OrderConfirmation(
        order: $order,
        tenant: $tenant,
        customerName: 'سارة علي',
        items: $items,
    );

    $html = $mail->render();

    expect($html)
        ->toContain('سارة علي')
        ->toContain('متجر التأكيد')
        ->toContain('#000001')
        ->toContain('منتج التأكيد')
        ->toContain('شحن إقليم')
        ->toContain('البطاقة الائتمانية');
});

<?php

use App\Livewire\Tenant\Pages\Checkout;
use App\Livewire\Tenant\Store\Detail;
use App\Mail\NewOrderNotification;
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
 * @return array{tenant: Tenant, owner: User, product: Content}
 */
function createNewOrderNotificationContext(): array
{
    $owner = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'مدير المتجر',
        'email' => 'owner@example.com',
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر الإشعارات',
        'handle' => 'notify-store-'.Str::lower(Str::random(6)),
        'user_id' => $owner->id,
        'active' => true,
        'status' => 'active',
    ]);

    $product = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج الإشعار',
        'slug' => 'notify-product',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => [
            'price' => 25000,
            'body' => 'وصف المنتج',
        ],
    ]);

    return compact('tenant', 'owner', 'product');
}

it('queues a new order notification email to the tenant owner after checkout', function () {
    Mail::fake();

    ['tenant' => $tenant, 'owner' => $owner, 'product' => $product] = createNewOrderNotificationContext();

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

    Mail::assertQueued(NewOrderNotification::class, function (NewOrderNotification $mail) use ($order, $tenant, $owner) {
        return $mail->hasTo('owner@example.com')
            && $mail->order->is($order)
            && $mail->tenant->is($tenant)
            && $mail->owner->is($owner)
            && $mail->customerName === 'أحمد محمد'
            && $mail->customerPhone === '0500000000'
            && $mail->customerEmail === 'ahmad@example.com'
            && $mail->items->count() === 1
            && $mail->orderDetailUrl === route('admin.orders.detail', ['id' => $order->uuid]);
    });

    Mail::assertQueued(OrderConfirmation::class);
});

it('does not queue a new order notification email when tenant owner has no email', function () {
    Mail::fake();

    ['tenant' => $tenant, 'product' => $product] = createNewOrderNotificationContext();

    $tenant->user->update(['email' => '']);

    setCurrentTenant($tenant);

    Setting::savePaymentMethod('cash-on-delivery', [], true);

    $shippingMethod = enableStoreCheckoutShipping(25);

    Livewire::test(Detail::class, ['slug' => $product->slug])
        ->call('addToCart');

    fillCheckoutShipping(Livewire::test(Checkout::class))
        ->set('name', 'عميل')
        ->set('phone', '0511111111')
        ->set('email', 'client@example.com')
        ->set('shippingMethod', $shippingMethod)
        ->set('paymentMethod', 'cash-on-delivery')
        ->call('confirmCashOnDelivery');

    Mail::assertNotQueued(NewOrderNotification::class);
});

it('renders the new order notification email with order and payment details', function () {
    ['tenant' => $tenant, 'owner' => $owner] = createNewOrderNotificationContext();

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
        'number' => '000042',
        'client_id' => $client->id,
        'currency_code' => 'SAR',
        'subtotal' => 50000,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => 52500,
        'paid_total' => 52500,
        'due_total' => 0,
        'payment_status' => 'paid',
        'issued_at' => now(),
        'financial_status' => 'open',
        'fulfillment_status' => 'unfulfilled',
        'meta' => [
            'payment_method' => 'credit-card',
            'shipping_method' => app(CheckoutShippingService::class)->registryMethodKey('eqleem-ship'),
            'shipping_method_label' => 'شحن إقليم',
            'shipping_fee' => 2500,
            'source' => 'store_cart',
        ],
    ]);

    DB::table('order_items')->insert([
        'order_id' => $order->id,
        'name' => 'منتج الإشعار',
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

    $mail = new NewOrderNotification(
        order: $order,
        tenant: $tenant,
        owner: $owner,
        customerName: 'سارة علي',
        customerPhone: '0522222222',
        customerEmail: 'sara@example.com',
        items: $items,
        orderDetailUrl: route('admin.orders.detail', ['id' => $order->uuid]),
    );

    $html = $mail->render();

    expect($html)
        ->toContain('مدير المتجر')
        ->toContain('لديك طلب جديد')
        ->toContain('متجر الإشعارات')
        ->toContain('#000042')
        ->toContain('سارة علي')
        ->toContain('0522222222')
        ->toContain('منتج الإشعار')
        ->toContain('تفاصيل الدفع')
        ->toContain($order->paymentMethodLabel())
        ->toContain('مدفوع')
        ->toContain('عرض الطلب في لوحة التحكم');
});

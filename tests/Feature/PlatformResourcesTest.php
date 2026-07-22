<?php

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Widgets\StatsOverview;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use LucasDotVin\Soulbscription\Models\Subscription;

uses(RefreshDatabase::class);

function createPlatformTenant(User $owner, string $handle): Tenant
{
    $id = DB::table('tenants')->insertGetId([
        'uuid' => (string) Str::uuid(),
        'name' => 'إقليم '.$handle,
        'handle' => $handle,
        'user_id' => $owner->id,
        'active' => true,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return Tenant::query()->findOrFail($id);
}

it('lists subscriptions without create edit or delete', function () {
    $user = User::factory()->create();
    $tenant = createPlatformTenant($user, 'sub-tenant');

    Plan::query()->create([
        'id' => 1,
        'name' => 'free',
        'slug' => 'free',
        'label' => 'مجانية',
        'price' => 0,
        'grace_days' => 0,
        'is_system' => true,
        'active' => true,
    ]);

    $plan = Plan::query()->create([
        'name' => 'باقة النمو',
        'slug' => 'growth-'.Str::lower(Str::random(4)),
        'price' => 9900,
        'periodicity' => 1,
        'periodicity_type' => 'Month',
        'grace_days' => 0,
        'is_system' => true,
        'active' => true,
    ]);

    $subscriptionId = DB::table('subscriptions')->insertGetId([
        'plan_id' => $plan->id,
        'subscriber_type' => Tenant::class,
        'subscriber_id' => $tenant->id,
        'started_at' => now()->toDateString(),
        'expired_at' => now()->addMonth(),
        'canceled_at' => null,
        'suppressed_at' => null,
        'was_switched' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $subscription = Subscription::query()->withoutGlobalScopes()->findOrFail($subscriptionId);

    $this->actingAs($user);

    expect(SubscriptionResource::canCreate())->toBeFalse()
        ->and(SubscriptionResource::canEdit($subscription))->toBeFalse()
        ->and(SubscriptionResource::canDelete($subscription))->toBeFalse()
        ->and($plan->id)->not->toBe(1);

    Livewire::test(ListSubscriptions::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$subscription])
        ->assertSee('إقليم sub-tenant')
        ->assertSee('باقة النمو')
        ->assertSee('نشط');
});

it('lists payments without create edit or delete', function () {
    $user = User::factory()->create();
    $tenant = createPlatformTenant($user, 'pay-tenant');

    $order = Order::query()->withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => 'order',
        'status' => 'completed',
        'channel' => 'ecommerce',
        'number' => 'ORD-'.Str::upper(Str::random(5)),
        'currency_code' => 'SAR',
        'subtotal' => 15000,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => 15000,
        'paid_total' => 15000,
        'due_total' => 0,
        'payment_status' => 'paid',
        'issued_at' => now(),
        'financial_status' => 'paid',
        'fulfillment_status' => 'fulfilled',
    ]);

    $payment = Payment::query()->withoutGlobalScope('tenant')->create([
        'uuid' => (string) Str::uuid(),
        'tenant_id' => $tenant->id,
        'order_id' => $order->id,
        'amount' => 15000,
        'currency' => 'SAR',
        'paymentable_type' => Order::class,
        'paymentable_id' => $order->id,
        'captured' => true,
        'reason' => 'client-buy-from-tenant',
        'gateway' => 'moyasar',
        'initial_status' => 'paid',
    ]);

    $this->actingAs($user);

    expect(PaymentResource::canCreate())->toBeFalse()
        ->and(PaymentResource::canEdit($payment))->toBeFalse()
        ->and(PaymentResource::canDelete($payment))->toBeFalse();

    Livewire::test(ListPayments::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$payment])
        ->assertSee('إقليم pay-tenant')
        ->assertSee('شراء من المتجر');
});

it('lists orders and shows order items on the view page', function () {
    $user = User::factory()->create();
    $tenant = createPlatformTenant($user, 'order-tenant');

    $client = Client::query()->create([
        'uuid' => (string) Str::uuid(),
        'tenant_id' => $tenant->id,
        'name' => 'عميل الطلب',
        'email' => 'client@example.com',
        'phone' => '0501112233',
        'active' => true,
    ]);

    $order = Order::query()->withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'type' => 'order',
        'status' => 'confirmed',
        'channel' => 'ecommerce',
        'number' => 'ORD-VIEW1',
        'currency_code' => 'SAR',
        'subtotal' => 20000,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => 20000,
        'paid_total' => 0,
        'due_total' => 20000,
        'payment_status' => 'unpaid',
        'issued_at' => now(),
        'financial_status' => 'open',
        'fulfillment_status' => 'unfulfilled',
    ]);

    OrderItem::query()->create([
        'order_id' => $order->id,
        'name' => 'منتج تجريبي',
        'qty' => 2,
        'unit_price' => 10000,
        'discount_total' => 0,
        'tax_total' => 0,
        'line_total' => 20000,
        'meta' => ['type' => 'product'],
    ]);

    $this->actingAs($user);

    expect(OrderResource::canCreate())->toBeFalse()
        ->and(OrderResource::canEdit($order))->toBeFalse()
        ->and(OrderResource::canDelete($order))->toBeFalse();

    Livewire::test(ListOrders::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$order])
        ->assertSee('ORD-VIEW1')
        ->assertSee('عميل الطلب')
        ->assertSee('إقليم order-tenant');

    Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('ORD-VIEW1')
        ->assertSee('منتج تجريبي')
        ->assertSee('عناصر الطلب');
});

it('lists clients with registration tenant and tenants count', function () {
    $user = User::factory()->create();
    $homeTenant = createPlatformTenant($user, 'home-tenant');
    $otherTenant = createPlatformTenant($user, 'other-tenant');

    $client = Client::query()->create([
        'uuid' => (string) Str::uuid(),
        'tenant_id' => $homeTenant->id,
        'name' => 'سارة العميل',
        'email' => 'sara@example.com',
        'phone' => '0509998877',
        'active' => true,
    ]);

    $client->tenants()->attach([
        $homeTenant->id => ['active' => true],
        $otherTenant->id => ['active' => true],
    ]);

    $this->actingAs($user);

    expect(ClientResource::canCreate())->toBeFalse()
        ->and(ClientResource::canEdit($client))->toBeFalse()
        ->and(ClientResource::canDelete($client))->toBeFalse();

    Livewire::test(ListClients::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$client->fresh()])
        ->assertSee('سارة العميل')
        ->assertSee('sara@example.com')
        ->assertSee('إقليم home-tenant')
        ->assertSee('2');

    Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('سارة العميل')
        ->assertSee('مسجّل في الأقاليم')
        ->assertSee('إقليم other-tenant');
});

it('links subscription payment and order widgets to their resources', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(StatsOverview::class)
        ->assertSuccessful()
        ->assertSee(SubscriptionResource::getUrl('index'), false)
        ->assertSee(PaymentResource::getUrl('index'), false)
        ->assertSee(OrderResource::getUrl('index'), false);
});

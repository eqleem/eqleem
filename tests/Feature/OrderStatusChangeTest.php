<?php

use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);
});

function createTenantWithUser(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'test-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    return [$user, $tenant];
}

function createOrderForTenant(Tenant $tenant, array $overrides = []): Order
{
    return Order::create(array_merge([
        'tenant_id' => $tenant->id,
        'type' => 'order',
        'status' => 'draft',
        'channel' => 'manual',
        'number' => 'ORD-'.Str::upper(Str::random(6)),
        'currency_code' => 'SAR',
        'subtotal' => 10000,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => 10000,
        'paid_total' => 0,
        'due_total' => 10000,
        'payment_status' => 'unpaid',
        'issued_at' => now(),
        'financial_status' => 'draft',
        'fulfillment_status' => 'unfulfilled',
    ], $overrides));
}

it('changes order status and stores the reason', function () {
    [, $tenant] = createTenantWithUser();

    $order = createOrderForTenant($tenant);

    $order->changeStatus('confirmed', 'تم التأكيد مع العميل');

    $order->refresh();
    $status = $order->statuses()->first();

    expect($order->statusValue())->toBe('confirmed')
        ->and($status)->not->toBeNull()
        ->and($status->name)->toBe('confirmed')
        ->and($status->reason)->toBe('تم التأكيد مع العميل');
});

it('renders order detail page with activity history section', function () {
    [$user, $tenant] = createTenantWithUser();

    $order = createOrderForTenant($tenant);
    $order->changeStatus('awaiting_payment', 'فتح الطلب للمراجعة');

    $this->actingAs($user)
        ->get(route('admin.orders.detail', ['id' => $order->uuid]))
        ->assertSuccessful()
        ->assertSee('سجل النشاط')
        ->assertSee('تغيير الحالة')
        ->assertSee('فتح الطلب للمراجعة');
});

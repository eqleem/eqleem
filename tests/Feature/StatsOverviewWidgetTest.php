<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\StatsOverview;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use LucasDotVin\Soulbscription\Models\Subscription;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createStatsTenant(User $owner, string $handle, ?Carbon $createdAt = null): array
{
    $now = $createdAt ?? now();

    $id = DB::table('tenants')->insertGetId([
        'uuid' => (string) Str::uuid(),
        'name' => 'Tenant '.$handle,
        'handle' => $handle,
        'user_id' => $owner->id,
        'active' => true,
        'status' => 'active',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return [$owner, Tenant::query()->findOrFail($id)];
}

function createStatsOrder(Tenant $tenant, ?Carbon $createdAt = null): Order
{
    return Order::query()->withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => 'order',
        'status' => 'open',
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
        'issued_at' => $createdAt ?? now(),
        'financial_status' => 'open',
        'fulfillment_status' => 'unfulfilled',
        'created_at' => $createdAt ?? now(),
        'updated_at' => $createdAt ?? now(),
    ]);
}

function createStatsPayment(Tenant $tenant, Order $order, int $amount, ?Carbon $createdAt = null): Payment
{
    return Payment::query()->withoutGlobalScope('tenant')->create([
        'uuid' => (string) Str::uuid(),
        'tenant_id' => $tenant->id,
        'order_id' => $order->id,
        'amount' => $amount,
        'currency' => 'SAR',
        'paymentable_type' => Order::class,
        'paymentable_id' => $order->id,
        'captured' => true,
        'reason' => 'order',
        'gateway' => 'cash',
        'created_at' => $createdAt ?? now(),
        'updated_at' => $createdAt ?? now(),
    ]);
}

function createStatsSubscription(Tenant $tenant, Plan $plan, ?Carbon $createdAt = null): Subscription
{
    $now = $createdAt ?? now();

    $id = DB::table('subscriptions')->insertGetId([
        'plan_id' => $plan->id,
        'subscriber_type' => Tenant::class,
        'subscriber_id' => $tenant->id,
        'started_at' => $now->toDateString(),
        'expired_at' => $now->copy()->addMonth(),
        'canceled_at' => null,
        'suppressed_at' => null,
        'was_switched' => false,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return Subscription::query()->withoutGlobalScopes()->findOrFail($id);
}

it('hides the dashboard page heading', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test(Dashboard::class);

    $component->assertSuccessful();

    expect($component->instance()->getHeading())->toBeNull();
});

it('shows total users with percentage increase from the previous period', function () {
    User::factory()->count(5)->create(['created_at' => now()->subDays(45)]);
    User::factory()->count(10)->create(['created_at' => now()->subDays(10)]);

    $user = User::factory()->create(['created_at' => now()->subDays(10)]);

    $this->actingAs($user);

    Livewire::test(StatsOverview::class)
        ->assertSuccessful()
        ->assertSee('إجمالي المستخدمين')
        ->assertSee('16')
        ->assertSee('120% زيادة');
});

it('shows a percentage decrease when the current period has fewer new users', function () {
    User::factory()->count(10)->create(['created_at' => now()->subDays(45)]);
    User::factory()->count(5)->create(['created_at' => now()->subDays(10)]);

    $user = User::factory()->create(['created_at' => now()->subDays(10)]);

    $this->actingAs($user);

    Livewire::test(StatsOverview::class)
        ->assertSuccessful()
        ->assertSee('إجمالي المستخدمين')
        ->assertSee('16')
        ->assertSee('40% انخفاض');
});

it('shows tenants, subscriptions, sales and orders stats', function () {
    $user = User::factory()->create(['created_at' => now()->subDays(60)]);

    createStatsTenant($user, 'old-tenant-'.Str::lower(Str::random(4)), now()->subDays(45));
    [, $currentTenant] = createStatsTenant($user, 'new-tenant-'.Str::lower(Str::random(4)), now()->subDays(10));

    $plan = Plan::query()->create([
        'name' => 'Test Plan',
        'slug' => 'test-plan-'.Str::lower(Str::random(4)),
        'price' => 1000,
        'periodicity' => 1,
        'periodicity_type' => 'Month',
        'grace_days' => 0,
        'is_system' => true,
        'active' => true,
    ]);

    createStatsSubscription($currentTenant, $plan, now()->subDays(45));
    createStatsSubscription($currentTenant, $plan, now()->subDays(10));
    createStatsSubscription($currentTenant, $plan, now()->subDays(5));

    $oldOrder = createStatsOrder($currentTenant, now()->subDays(45));
    createStatsPayment($currentTenant, $oldOrder, 10000, now()->subDays(45));

    $newOrderA = createStatsOrder($currentTenant, now()->subDays(10));
    $newOrderB = createStatsOrder($currentTenant, now()->subDays(5));
    createStatsPayment($currentTenant, $newOrderA, 20000, now()->subDays(10));
    createStatsPayment($currentTenant, $newOrderB, 30000, now()->subDays(5));

    $this->actingAs($user);

    Livewire::test(StatsOverview::class)
        ->assertSuccessful()
        ->assertSee('الأقاليم')
        ->assertSee('المشتركين')
        ->assertSee('إجمالي المبيعات')
        ->assertSee('الطلبات')
        ->assertSee(Money::formatWithCurrency(60000))
        ->assertSee('2')
        ->assertSee('3')
        ->assertSee('100% زيادة')
        ->assertSee('400% زيادة');
});

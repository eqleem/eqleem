<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RequestAnalytics;
use App\Models\Tenant;
use App\Models\User;
use App\Support\DashboardStats;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createDashboardStatsUserWithTenant(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'stats-owner-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'stats-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

function createClientForDashboardStats(Tenant $tenant, array $overrides = []): Client
{
    setCurrentTenant($tenant);

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'عميل الإحصائيات',
        'email' => 'stats-client-'.Str::lower(Str::random(6)).'@example.com',
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

function createOrderForDashboardStats(Tenant $tenant, array $overrides = []): Order
{
    setCurrentTenant($tenant);

    return Order::query()->withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'type' => 'order',
        'status' => 'open',
        'channel' => 'manual',
        'number' => '000'.random_int(100, 999),
        'currency_code' => 'SAR',
        'subtotal' => 10000,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => 10000,
        'paid_total' => 0,
        'due_total' => 10000,
        'payment_status' => 'unpaid',
        'issued_at' => now(),
        'financial_status' => 'open',
        'fulfillment_status' => 'unfulfilled',
        ...$overrides,
    ]);
}

function createPaymentForDashboardStats(Tenant $tenant, Order $order, int $amount = 5000): Payment
{
    setCurrentTenant($tenant);

    return Payment::query()->withoutGlobalScopes()->create([
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
    ]);
}

function createVisitForDashboardStats(Tenant $tenant, array $overrides = []): RequestAnalytics
{
    return RequestAnalytics::withoutGlobalScopes()->create(array_merge([
        'tenant_id' => $tenant->id,
        'path' => '/',
        'ip_address' => '127.0.0.1',
        'session_id' => (string) Str::uuid(),
        'http_method' => 'GET',
        'request_category' => 'web',
        'visited_at' => now(),
    ], $overrides));
}

test('guests cannot fetch dashboard stats', function () {
    $this->getJson('/api/dashboard/stats')
        ->assertUnauthorized();
});

test('inactive tenant cannot fetch dashboard stats', function () {
    [$user] = createDashboardStatsUserWithTenant(['active' => false]);

    $this->actingAs($user)
        ->getJson('/api/dashboard/stats')
        ->assertForbidden();
});

test('owner receives month summary stats for current tenant only', function () {
    Cache::flush();

    [$user, $tenant] = createDashboardStatsUserWithTenant();
    createOrderForDashboardStats($tenant);
    createOrderForDashboardStats($tenant);
    $order = createOrderForDashboardStats($tenant);
    createPaymentForDashboardStats($tenant, $order, 2500);
    createClientForDashboardStats($tenant);
    createClientForDashboardStats($tenant);
    createVisitForDashboardStats($tenant);
    createVisitForDashboardStats($tenant);
    createVisitForDashboardStats($tenant);

    [$otherUser, $otherTenant] = createDashboardStatsUserWithTenant();
    createOrderForDashboardStats($otherTenant);
    createClientForDashboardStats($otherTenant);
    createVisitForDashboardStats($otherTenant);

    $response = $this->actingAs($user)
        ->getJson('/api/dashboard/stats')
        ->assertSuccessful()
        ->assertJsonPath('data.orders.value', 3)
        ->assertJsonPath('data.clients.value', 2)
        ->assertJsonPath('data.visits.value', 3)
        ->assertJsonPath('data.sales.value', 2500);

    expect($response->json('data.sales.value_formatted'))
        ->toBe(Money::formatWithCurrency(2500, 'SAR'))
        ->and($response->json('data.range_days'))->toBeGreaterThan(0)
        ->and($response->json('data.orders.growth'))->toBeInt()
        ->and($response->json('data.sales.growth'))->toBeInt();
});

test('individual metric endpoints share the same cached payload', function () {
    Cache::flush();

    [$user, $tenant] = createDashboardStatsUserWithTenant();
    createOrderForDashboardStats($tenant);
    createClientForDashboardStats($tenant);
    createVisitForDashboardStats($tenant);

    $this->actingAs($user)
        ->getJson('/api/dashboard/stats/orders')
        ->assertSuccessful()
        ->assertJsonPath('data.metric', 'orders')
        ->assertJsonPath('data.value', 1);

    $this->actingAs($user)
        ->getJson('/api/dashboard/stats/clients')
        ->assertSuccessful()
        ->assertJsonPath('data.metric', 'clients')
        ->assertJsonPath('data.value', 1);

    $this->actingAs($user)
        ->getJson('/api/dashboard/stats/visits')
        ->assertSuccessful()
        ->assertJsonPath('data.metric', 'visits')
        ->assertJsonPath('data.value', 1);

    $this->actingAs($user)
        ->getJson('/api/dashboard/stats/unknown')
        ->assertNotFound();
});

test('stats are served from cache on subsequent requests', function () {
    Cache::flush();

    [$user, $tenant] = createDashboardStatsUserWithTenant();
    createOrderForDashboardStats($tenant);

    $this->actingAs($user)
        ->getJson('/api/dashboard/stats')
        ->assertSuccessful()
        ->assertJsonPath('data.orders.value', 1);

    createOrderForDashboardStats($tenant);

    // Still cached until forget / TTL / fresh=1
    $this->actingAs($user)
        ->getJson('/api/dashboard/stats')
        ->assertSuccessful()
        ->assertJsonPath('data.orders.value', 1);

    DashboardStats::forget($tenant);

    $this->actingAs($user)
        ->getJson('/api/dashboard/stats')
        ->assertSuccessful()
        ->assertJsonPath('data.orders.value', 2);

    createOrderForDashboardStats($tenant);

    $this->actingAs($user)
        ->getJson('/api/dashboard/stats?fresh=1')
        ->assertSuccessful()
        ->assertJsonPath('data.orders.value', 3);
});

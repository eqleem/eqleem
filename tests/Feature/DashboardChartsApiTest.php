<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RequestAnalytics;
use App\Models\Tenant;
use App\Models\User;
use App\Support\DashboardCharts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createDashboardChartsUserWithTenant(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'charts-owner-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'charts-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

function createClientForDashboardCharts(Tenant $tenant): Client
{
    setCurrentTenant($tenant);

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'عميل الرسم',
        'email' => 'charts-client-'.Str::lower(Str::random(6)).'@example.com',
        'phone' => '05'.random_int(10000000, 99999999),
        'tenant_id' => $tenant->id,
        'active' => true,
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

function createOrderForDashboardCharts(Tenant $tenant): Order
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
    ]);
}

function createPaymentForDashboardCharts(Tenant $tenant, Order $order, int $amount = 5000): Payment
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

function createVisitForDashboardCharts(Tenant $tenant): RequestAnalytics
{
    return RequestAnalytics::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'path' => '/',
        'ip_address' => '127.0.0.1',
        'session_id' => (string) Str::uuid(),
        'http_method' => 'GET',
        'request_category' => 'web',
        'visited_at' => now(),
    ]);
}

test('guests cannot fetch dashboard charts', function () {
    $this->getJson('/api/dashboard/charts/orders')
        ->assertUnauthorized();
});

test('inactive tenant cannot fetch dashboard charts', function () {
    [$user] = createDashboardChartsUserWithTenant(['active' => false]);

    $this->actingAs($user)
        ->getJson('/api/dashboard/charts/orders')
        ->assertForbidden();
});

test('unknown chart returns not found', function () {
    [$user] = createDashboardChartsUserWithTenant();

    $this->actingAs($user)
        ->getJson('/api/dashboard/charts/unknown')
        ->assertNotFound();
});

test('each chart endpoint returns HasChartWidget-shaped options independently', function (string $chart, string $title, string $label) {
    Cache::flush();

    [$user, $tenant] = createDashboardChartsUserWithTenant();
    $order = createOrderForDashboardCharts($tenant);
    createPaymentForDashboardCharts($tenant, $order, 2500);
    createClientForDashboardCharts($tenant);
    createVisitForDashboardCharts($tenant);

    $response = $this->actingAs($user)
        ->getJson("/api/dashboard/charts/{$chart}")
        ->assertSuccessful()
        ->assertJsonPath('data.chart', $chart)
        ->assertJsonPath('data.title', $title)
        ->assertJsonPath('data.label', $label)
        ->assertJsonPath('data.options.type', 'line')
        ->assertJsonPath('data.options.rtl', true)
        ->assertJsonPath('data.options.locale', 'ar')
        ->assertJsonPath('data.options.data.datasets.0.label', $label)
        ->assertJsonPath('data.options.data.datasets.0.borderColor', '#36A2EB');

    expect($response->json('data.range_days'))->toBeGreaterThan(0)
        ->and($response->json('data.options.data.labels'))->toBeArray()->not->toBeEmpty()
        ->and($response->json('data.options.data.datasets.0.data'))->toBeArray()->not->toBeEmpty();
})->with([
    'orders' => ['orders', 'الطلبات', 'العدد'],
    'sales' => ['sales', 'المبيعات', 'المبيعات'],
    'visits' => ['visits', 'الزيارات', 'العدد'],
    'clients' => ['clients', 'العملاء', 'العدد'],
]);

test('chart endpoints are cached separately and refresh with fresh=1', function () {
    Cache::flush();

    [$user, $tenant] = createDashboardChartsUserWithTenant();
    createOrderForDashboardCharts($tenant);

    $first = $this->actingAs($user)
        ->getJson('/api/dashboard/charts/orders')
        ->assertSuccessful()
        ->json('data.options.data.datasets.0.data');

    createOrderForDashboardCharts($tenant);

    $cached = $this->actingAs($user)
        ->getJson('/api/dashboard/charts/orders')
        ->assertSuccessful()
        ->json('data.options.data.datasets.0.data');

    expect($cached)->toBe($first);

    DashboardCharts::forget($tenant);

    $refreshed = $this->actingAs($user)
        ->getJson('/api/dashboard/charts/orders?fresh=1')
        ->assertSuccessful()
        ->json('data.options.data.datasets.0.data');

    expect(array_sum($refreshed))->toBeGreaterThan(array_sum($first));
});

test('charts are tenant scoped', function () {
    Cache::flush();

    [$user, $tenant] = createDashboardChartsUserWithTenant();
    createOrderForDashboardCharts($tenant);
    createOrderForDashboardCharts($tenant);

    [, $otherTenant] = createDashboardChartsUserWithTenant();
    createOrderForDashboardCharts($otherTenant);

    $data = $this->actingAs($user)
        ->getJson('/api/dashboard/charts/orders')
        ->assertSuccessful()
        ->json('data.options.data.datasets.0.data');

    expect(array_sum($data))->toBe(2);
});

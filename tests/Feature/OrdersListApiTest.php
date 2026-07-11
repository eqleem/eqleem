<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createOrdersListUserWithTenant(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'أحمد الأحمدي',
        'email' => 'orders-owner-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'orders-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

function createClientForOrdersList(Tenant $tenant, array $overrides = []): Client
{
    setCurrentTenant($tenant);

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'محمد العتيبي',
        'email' => 'client-'.Str::lower(Str::random(6)).'@example.com',
        'phone' => '0512345678',
        'tenant_id' => $tenant->id,
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

function createOrderForOrdersList(Tenant $tenant, array $overrides = []): Order
{
    setCurrentTenant($tenant);

    return Order::query()->create([
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

test('guests cannot list orders', function () {
    $this->getJson('/api/orders')
        ->assertUnauthorized();
});

test('inactive tenant cannot list orders', function () {
    [$user] = createOrdersListUserWithTenant(['active' => false]);

    $this->actingAs($user)
        ->getJson('/api/orders')
        ->assertForbidden();
});

test('owner lists only current tenant orders with lean payload', function () {
    [$user, $tenant] = createOrdersListUserWithTenant();
    $client = createClientForOrdersList($tenant);

    $order = createOrderForOrdersList($tenant, [
        'client_id' => $client->id,
        'number' => '000777',
        'status' => 'completed',
        'payment_status' => 'paid',
        'grand_total' => 48000,
    ]);

    [$otherUser, $otherTenant] = createOrdersListUserWithTenant();
    createOrderForOrdersList($otherTenant, [
        'number' => '000999',
        'grand_total' => 99900,
    ]);

    $this->actingAs($user)
        ->getJson('/api/orders')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $order->id)
        ->assertJsonPath('data.0.uuid', $order->uuid)
        ->assertJsonPath('data.0.number', '000777')
        ->assertJsonPath('data.0.status', 'completed')
        ->assertJsonPath('data.0.status_label', 'مكتمل')
        ->assertJsonPath('data.0.status_color', 'green')
        ->assertJsonPath('data.0.payment_status', 'paid')
        ->assertJsonPath('data.0.payment_status_label', 'حالة الدفع: مدفوع')
        ->assertJsonPath('data.0.grand_total', 48000)
        ->assertJsonPath('data.0.client', 'محمد العتيبي')
        ->assertJsonMissingPath('data.0.meta')
        ->assertJsonMissingPath('data.0.notes')
        ->assertJsonMissingPath('data.0.subtotal');
});

test('search filters by order number and client name', function () {
    [$user, $tenant] = createOrdersListUserWithTenant();
    $client = createClientForOrdersList($tenant, ['name' => 'سارة القحطاني']);

    createOrderForOrdersList($tenant, [
        'client_id' => $client->id,
        'number' => '000111',
    ]);

    createOrderForOrdersList($tenant, [
        'number' => '000222',
        'client_id' => null,
    ]);

    $this->actingAs($user)
        ->getJson('/api/orders?search=سارة')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.number', '000111');

    $this->actingAs($user)
        ->getJson('/api/orders?search=000222')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.number', '000222');
});

test('walking client orders return null client name', function () {
    [$user, $tenant] = createOrdersListUserWithTenant();

    createOrderForOrdersList($tenant, [
        'client_id' => null,
        'number' => '000333',
    ]);

    $this->actingAs($user)
        ->getJson('/api/orders')
        ->assertSuccessful()
        ->assertJsonPath('data.0.client', null)
        ->assertJsonPath('data.0.number', '000333');
});

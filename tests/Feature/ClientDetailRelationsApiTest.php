<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createClientDetailApiUserWithTenant(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'client-detail-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'client-detail-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

function createClientForDetailApi(Tenant $tenant, array $overrides = []): Client
{
    setCurrentTenant($tenant);

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'محمد العتيبي',
        'email' => 'client-'.Str::lower(Str::random(6)).'@example.com',
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

function createOrderForDetailApi(Tenant $tenant, ?Client $client = null, array $overrides = []): Order
{
    setCurrentTenant($tenant);

    $order = Order::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client?->id,
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

    DB::table('order_items')->insert([
        'order_id' => $order->id,
        'name' => 'منتج تجريبي',
        'qty' => 1,
        'unit_price' => 10000,
        'line_total' => 10000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $order;
}

function createInvoiceForOrderDetailApi(Tenant $tenant, Order $order, array $overrides = []): Invoice
{
    setCurrentTenant($tenant);

    return Invoice::query()->create([
        'tenant_id' => $tenant->id,
        'invoicable_type' => Order::class,
        'invoicable_id' => $order->id,
        'amount_paid' => 5000,
        'total_before_vat' => 10000,
        'total_after_vat' => 10000,
        'subtotal_before_vat' => 10000,
        'subtotal_after_vat' => 10000,
        'currency' => 'SAR',
        'type' => 'sell',
        'initial_status' => 'partial',
        'issued_on' => now(),
        ...$overrides,
    ]);
}

test('lists only the clients orders for the current tenant', function () {
    [$user, $tenant] = createClientDetailApiUserWithTenant();
    $client = createClientForDetailApi($tenant);
    $order = createOrderForDetailApi($tenant, $client, ['number' => '000777']);

    $otherClient = createClientForDetailApi($tenant, ['name' => 'عميل آخر']);
    createOrderForDetailApi($tenant, $otherClient, ['number' => '000888']);

    [$otherUser, $otherTenant] = createClientDetailApiUserWithTenant();
    $foreignClient = createClientForDetailApi($otherTenant);
    createOrderForDetailApi($otherTenant, $foreignClient, ['number' => '000999']);

    $this->actingAs($user)
        ->getJson('/api/clients/'.$client->uuid.'/orders')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $order->uuid)
        ->assertJsonPath('data.0.number', '000777')
        ->assertJsonPath('data.0.items_count', 1)
        ->assertJsonMissingPath('data.0.meta')
        ->assertJsonMissingPath('data.0.notes');
});

test('client orders search filters by number', function () {
    [$user, $tenant] = createClientDetailApiUserWithTenant();
    $client = createClientForDetailApi($tenant);
    createOrderForDetailApi($tenant, $client, ['number' => '000111']);
    createOrderForDetailApi($tenant, $client, ['number' => '000222']);

    $this->actingAs($user)
        ->getJson('/api/clients/'.$client->uuid.'/orders?'.http_build_query(['search' => '000222']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.number', '000222');
});

test('client orders can be filtered by status', function () {
    [$user, $tenant] = createClientDetailApiUserWithTenant();
    $client = createClientForDetailApi($tenant);
    createOrderForDetailApi($tenant, $client, ['number' => '000111', 'status' => 'new']);
    createOrderForDetailApi($tenant, $client, ['number' => '000222', 'status' => 'completed']);

    $this->actingAs($user)
        ->getJson('/api/clients/'.$client->uuid.'/orders?'.http_build_query(['status' => 'completed']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.number', '000222')
        ->assertJsonPath('data.0.status', 'completed')
        ->assertJsonPath('data.0.status_label', 'مكتمل');

    $this->actingAs($user)
        ->getJson('/api/clients/'.$client->uuid.'/orders?'.http_build_query(['status' => 'not-a-status']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('lists only invoices for the clients orders', function () {
    [$user, $tenant] = createClientDetailApiUserWithTenant();
    $client = createClientForDetailApi($tenant);
    $order = createOrderForDetailApi($tenant, $client, ['number' => '000555']);
    $invoice = createInvoiceForOrderDetailApi($tenant, $order);

    $otherClient = createClientForDetailApi($tenant, ['name' => 'آخر']);
    $otherOrder = createOrderForDetailApi($tenant, $otherClient, ['number' => '000666']);
    createInvoiceForOrderDetailApi($tenant, $otherOrder);

    $this->actingAs($user)
        ->getJson('/api/clients/'.$client->uuid.'/invoices')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $invoice->uuid)
        ->assertJsonPath('data.0.s_number', $invoice->s_number)
        ->assertJsonPath('data.0.order_label', 'طلب #000555')
        ->assertJsonPath('data.0.order_uuid', $order->uuid);
});

test('foreign client relations return not found', function () {
    [$user] = createClientDetailApiUserWithTenant();
    [$otherUser, $otherTenant] = createClientDetailApiUserWithTenant();
    $foreignClient = createClientForDetailApi($otherTenant);

    $this->actingAs($user)
        ->getJson('/api/clients/'.$foreignClient->uuid.'/orders')
        ->assertNotFound();

    $this->actingAs($user)
        ->getJson('/api/clients/'.$foreignClient->uuid.'/invoices')
        ->assertNotFound();
});

<?php

use App\Actions\RecordOrderPayment;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);
});

function createTenantWithUserForClientDetail(): array
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

function createClientForTenant(Tenant $tenant): Client
{
    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'عميل تجريبي',
        'email' => 'client-'.Str::lower(Str::random(6)).'@example.com',
        'phone' => '0512345678',
        'tenant_id' => $tenant->id,
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

function createOrderForClient(Tenant $tenant, Client $client, int $grandTotal = 10000): Order
{
    return Order::create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'type' => 'order',
        'status' => 'open',
        'channel' => 'manual',
        'number' => '000'.random_int(100, 999),
        'currency_code' => 'SAR',
        'subtotal' => $grandTotal,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => $grandTotal,
        'paid_total' => 0,
        'due_total' => $grandTotal,
        'payment_status' => 'unpaid',
        'issued_at' => now(),
        'financial_status' => 'open',
        'fulfillment_status' => 'unfulfilled',
    ]);
}

it('renders invoices tab on client detail page', function () {
    [$user, $tenant] = createTenantWithUserForClientDetail();
    $client = createClientForTenant($tenant);
    $order = createOrderForClient($tenant, $client);

    $this->actingAs($user);
    RecordOrderPayment::run($order, 5000, 'cash');

    $this->get(route('admin.clients.detail', ['id' => $client->uuid, 'tab' => 'invoices']))
        ->assertSuccessful()
        ->assertSee('الفواتير');
});

it('lists client invoices in the invoices table component', function () {
    [$user, $tenant] = createTenantWithUserForClientDetail();
    $client = createClientForTenant($tenant);
    $order = createOrderForClient($tenant, $client);

    $this->actingAs($user);
    RecordOrderPayment::run($order, 5000, 'cash');

    $invoice = Invoice::query()->where('tenant_id', $tenant->id)->firstOrFail();

    Livewire::test('admin::clients.invoices-table', ['client' => $client])
        ->assertSee($invoice->s_number);
});

it('does not show invoices from other clients orders', function () {
    [$user, $tenant] = createTenantWithUserForClientDetail();
    $client = createClientForTenant($tenant);
    $otherClient = createClientForTenant($tenant);

    $clientOrder = createOrderForClient($tenant, $client);
    $otherOrder = createOrderForClient($tenant, $otherClient);

    $this->actingAs($user);
    RecordOrderPayment::run($clientOrder, 5000, 'cash');
    RecordOrderPayment::run($otherOrder, 5000, 'cash');

    $clientInvoice = Invoice::query()
        ->where('invoicable_id', $clientOrder->id)
        ->firstOrFail();
    $otherInvoice = Invoice::query()
        ->where('invoicable_id', $otherOrder->id)
        ->firstOrFail();

    Livewire::test('admin::clients.invoices-table', ['client' => $client])
        ->assertSee($clientInvoice->s_number)
        ->assertDontSee($otherInvoice->s_number);
});

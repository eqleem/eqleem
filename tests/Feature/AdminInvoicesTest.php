<?php

use App\Actions\RecordOrderPayment;
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

function createTenantWithUserForInvoices(): array
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

function createUnpaidOrderForInvoices(Tenant $tenant, int $grandTotal = 10000): Order
{
    return Order::create([
        'tenant_id' => $tenant->id,
        'type' => 'order',
        'status' => 'open',
        'channel' => 'manual',
        'number' => '000001',
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

it('renders invoices tab on orders home page', function () {
    [$user, $tenant] = createTenantWithUserForInvoices();

    $order = createUnpaidOrderForInvoices($tenant);
    RecordOrderPayment::run($order, 5000, 'cash');

    $this->actingAs($user)
        ->get(route('admin.orders.home', ['tab' => 'invoices']))
        ->assertSuccessful()
        ->assertSee('الفواتير');
});

it('renders invoice detail page with items and totals', function () {
    [$user, $tenant] = createTenantWithUserForInvoices();

    $order = createUnpaidOrderForInvoices($tenant);
    RecordOrderPayment::run($order, 5000, 'cash');

    $invoice = Invoice::query()->where('tenant_id', $tenant->id)->firstOrFail();

    $this->actingAs($user)
        ->get(route('admin.orders.invoices.detail', ['uuid' => $invoice->uuid]))
        ->assertSuccessful()
        ->assertSee($invoice->s_number)
        ->assertSee('بنود الفاتورة')
        ->assertSee('ملخص الفاتورة');
});

it('does not show invoices from other tenants', function () {
    [$user, $tenant] = createTenantWithUserForInvoices();
    [, $otherTenant] = createTenantWithUserForInvoices();

    setCurrentTenant($otherTenant);
    $otherOrder = createUnpaidOrderForInvoices($otherTenant);
    RecordOrderPayment::run($otherOrder, 5000, 'cash');
    $otherInvoice = Invoice::query()->where('tenant_id', $otherTenant->id)->firstOrFail();

    setCurrentTenant($tenant);

    $this->actingAs($user)
        ->get(route('admin.orders.invoices.detail', ['uuid' => $otherInvoice->uuid]))
        ->assertNotFound();
});

it('lists tenant invoices in the invoices table component', function () {
    [$user, $tenant] = createTenantWithUserForInvoices();

    $order = createUnpaidOrderForInvoices($tenant);
    RecordOrderPayment::run($order, 5000, 'cash');

    $invoice = Invoice::query()->where('tenant_id', $tenant->id)->firstOrFail();

    $this->actingAs($user);

    Livewire::test('admin::orders.invoices-table')
        ->assertSee($invoice->s_number);
});

<?php

use App\Actions\RecordOrderPayment;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);
});

function createTenantWithUserForPayment(): array
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

function createUnpaidOrderForTenant(Tenant $tenant, int $grandTotal = 10000, array $items = []): Order
{
    $order = Order::create([
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
        'financial_status' => 'draft',
        'fulfillment_status' => 'unfulfilled',
    ]);

    if ($items === []) {
        $items = [
            [
                'name' => 'منتج تجريبي',
                'qty' => 2,
                'unit_price' => (int) ($grandTotal / 2),
                'discount_total' => 0,
                'tax_total' => 0,
                'line_total' => $grandTotal,
                'type' => 'product',
            ],
        ];
    }

    foreach ($items as $item) {
        DB::table('order_items')->insert([
            'order_id' => $order->id,
            'product_id' => $item['product_id'] ?? null,
            'sku' => $item['sku'] ?? null,
            'name' => $item['name'],
            'qty' => $item['qty'],
            'unit_price' => $item['unit_price'],
            'discount_total' => $item['discount_total'],
            'tax_total' => $item['tax_total'],
            'line_total' => $item['line_total'],
            'meta' => json_encode([
                'type' => $item['type'] ?? 'product',
                'description' => $item['description'] ?? null,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return $order;
}

it('records an order payment with invoice', function () {
    [$user, $tenant] = createTenantWithUserForPayment();
    $order = createUnpaidOrderForTenant($tenant);

    $this->actingAs($user);

    $payment = RecordOrderPayment::run($order, 5000, 'cash', 'دفعة أولى');

    $order->refresh();
    $invoice = Invoice::query()->where('invoicable_type', Order::class)
        ->where('invoicable_id', $order->id)
        ->first();

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->order_id)->toBe($order->id)
        ->and($payment->invoice_id)->toBe($invoice->id)
        ->and($payment->amount)->toBe(5000)
        ->and($payment->initial_status)->toBe('paid')
        ->and($invoice)->not->toBeNull()
        ->and($invoice->amount_paid)->toBe(5000)
        ->and($invoice->total_after_vat)->toBe(10000)
        ->and($invoice->invoicable_id)->toBe($order->id)
        ->and($order->paid_total)->toBe(5000)
        ->and($order->due_total)->toBe(5000)
        ->and($order->payment_status)->toBe('partial');

    $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();

    expect($invoiceItems)->toHaveCount(1)
        ->and($invoiceItems->first()->name)->toBe('منتج تجريبي')
        ->and($invoiceItems->first()->type)->toBe('product')
        ->and($invoiceItems->first()->quantity)->toBe(2)
        ->and($invoiceItems->first()->total_after_vat)->toBe(10000);
});

it('copies all order items to invoice items', function () {
    [$user, $tenant] = createTenantWithUserForPayment();

    $order = createUnpaidOrderForTenant($tenant, 15000, [
        [
            'name' => 'خدمة استشارية',
            'qty' => 1,
            'unit_price' => 5000,
            'discount_total' => 0,
            'tax_total' => 0,
            'line_total' => 5000,
            'type' => 'service',
        ],
        [
            'name' => 'دورة تدريبية',
            'qty' => 1,
            'unit_price' => 10000,
            'discount_total' => 0,
            'tax_total' => 0,
            'line_total' => 10000,
            'type' => 'course',
        ],
    ]);

    $this->actingAs($user);

    $payment = RecordOrderPayment::run($order, 15000, 'cash');

    $invoiceItems = DB::table('invoice_items')
        ->where('invoice_id', $payment->invoice_id)
        ->orderBy('id')
        ->get();

    expect($invoiceItems)->toHaveCount(2)
        ->and($invoiceItems->pluck('name')->all())->toBe(['خدمة استشارية', 'دورة تدريبية'])
        ->and($invoiceItems->pluck('type')->all())->toBe(['service', 'course']);
});

it('marks order as paid when full amount is received', function () {
    [$user, $tenant] = createTenantWithUserForPayment();
    $order = createUnpaidOrderForTenant($tenant, 7500);

    $this->actingAs($user);

    RecordOrderPayment::run($order, 7500, 'card');

    $order->refresh();

    expect($order->paid_total)->toBe(7500)
        ->and($order->due_total)->toBe(0)
        ->and($order->payment_status)->toBe('paid');
});

it('renders payments section on order detail page', function () {
    [$user, $tenant] = createTenantWithUserForPayment();
    $order = createUnpaidOrderForTenant($tenant);

    $this->actingAs($user);

    RecordOrderPayment::run($order, 2500, 'cash');

    $this->get(route('admin.orders.detail', ['id' => $order->uuid]))
        ->assertSuccessful()
        ->assertSee('المدفوعات')
        ->assertSee('تسجيل دفعة')
        ->assertSee('دفعة #');
});

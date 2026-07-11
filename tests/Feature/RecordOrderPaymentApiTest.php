<?php

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant, 2: Order}
 */
function createOrderForPaymentApi(array $orderOverrides = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'pay-order-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'pay-order-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    $order = Order::query()->create([
        'tenant_id' => $tenant->id,
        'type' => 'order',
        'status' => 'draft',
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
        'financial_status' => 'draft',
        'fulfillment_status' => 'unfulfilled',
        ...$orderOverrides,
    ]);

    DB::table('order_items')->insert([
        'order_id' => $order->id,
        'name' => 'منتج تجريبي',
        'qty' => 1,
        'unit_price' => 10000,
        'discount_total' => 0,
        'line_total' => 10000,
        'meta' => json_encode(['type' => 'product']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [$user->fresh(), $tenant->fresh(), $order->fresh()];
}

test('guests cannot record order payments', function () {
    [, , $order] = createOrderForPaymentApi();

    $this->postJson('/api/orders/'.$order->uuid.'/payments', [
        'amount' => 50,
        'method' => 'cash',
    ])->assertUnauthorized();
});

test('owner can record a partial payment and invoice is created', function () {
    [$user, , $order] = createOrderForPaymentApi();

    $this->actingAs($user)
        ->postJson('/api/orders/'.$order->uuid.'/payments', [
            'amount' => 40,
            'method' => 'cash',
            'notes' => 'دفعة أولى',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.uuid', $order->uuid)
        ->assertJsonPath('data.payment_status', 'partial')
        ->assertJsonPath('data.paid_total', 4000)
        ->assertJsonPath('data.due_total', 6000)
        ->assertJsonPath('data.payments.0.amount', 4000);

    expect(Payment::query()->where('order_id', $order->id)->count())->toBe(1)
        ->and(Invoice::query()->count())->toBe(1);

    $order->refresh();

    expect($order->payment_status)->toBe('partial')
        ->and((int) $order->paid_total)->toBe(4000)
        ->and((int) $order->due_total)->toBe(6000);
});

test('owner can fully pay an order', function () {
    [$user, , $order] = createOrderForPaymentApi();

    $this->actingAs($user)
        ->postJson('/api/orders/'.$order->uuid.'/payments', [
            'amount' => 100,
            'method' => 'bank_transfer',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.payment_status', 'paid')
        ->assertJsonPath('data.due_total', 0);
});

test('payment amount cannot exceed remaining balance', function () {
    [$user, , $order] = createOrderForPaymentApi();

    $this->actingAs($user)
        ->postJson('/api/orders/'.$order->uuid.'/payments', [
            'amount' => 150,
            'method' => 'cash',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['amount']);
});

test('foreign tenant cannot record payment on another order', function () {
    [$user] = createOrderForPaymentApi();
    [, , $otherOrder] = createOrderForPaymentApi();

    $this->actingAs($user)
        ->postJson('/api/orders/'.$otherOrder->uuid.'/payments', [
            'amount' => 10,
            'method' => 'cash',
        ])
        ->assertNotFound();
});

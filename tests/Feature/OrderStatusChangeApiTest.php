<?php

use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant, 2: Order}
 */
function createOrderForStatusApi(array $orderOverrides = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'status-order-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'status-order-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    $order = Order::query()->create([
        'tenant_id' => $tenant->id,
        'type' => 'order',
        'status' => 'new',
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

    return [$user->fresh(), $tenant->fresh(), $order->fresh()];
}

test('guests cannot update order status', function () {
    [, , $order] = createOrderForStatusApi();

    $this->patchJson('/api/orders/'.$order->uuid.'/status', [
        'status' => 'confirmed',
    ])->assertUnauthorized();
});

test('owner can update order status with a reason', function () {
    [$user, , $order] = createOrderForStatusApi();

    $this->actingAs($user)
        ->patchJson('/api/orders/'.$order->uuid.'/status', [
            'status' => 'confirmed',
            'reason' => 'تم التأكيد مع العميل',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.uuid', $order->uuid)
        ->assertJsonPath('data.status', 'confirmed');

    $order->refresh();
    $status = $order->statuses()->first();

    expect($order->statusValue())->toBe('confirmed')
        ->and($status)->not->toBeNull()
        ->and($status->name)->toBe('confirmed')
        ->and($status->reason)->toBe('تم التأكيد مع العميل');
});

test('owner can update order status without a reason', function () {
    [$user, , $order] = createOrderForStatusApi();

    $this->actingAs($user)
        ->patchJson('/api/orders/'.$order->uuid.'/status', [
            'status' => 'awaiting_payment',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'awaiting_payment');

    $order->refresh();
    $status = $order->statuses()->first();

    expect($order->statusValue())->toBe('awaiting_payment')
        ->and($status)->not->toBeNull()
        ->and($status->name)->toBe('awaiting_payment')
        ->and($status->reason)->toBeNull();
});

test('cannot set the same status again', function () {
    [$user, , $order] = createOrderForStatusApi(['status' => 'confirmed']);

    $this->actingAs($user)
        ->patchJson('/api/orders/'.$order->uuid.'/status', [
            'status' => 'confirmed',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('status must be a valid option', function () {
    [$user, , $order] = createOrderForStatusApi();

    $this->actingAs($user)
        ->patchJson('/api/orders/'.$order->uuid.'/status', [
            'status' => 'not-a-real-status',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('foreign tenant cannot update another order status', function () {
    [$user] = createOrderForStatusApi();
    [, , $otherOrder] = createOrderForStatusApi();

    $this->actingAs($user)
        ->patchJson('/api/orders/'.$otherOrder->uuid.'/status', [
            'status' => 'confirmed',
        ])
        ->assertNotFound();
});

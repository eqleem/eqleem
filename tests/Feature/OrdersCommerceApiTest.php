<?php

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Content;
use App\Models\FormSubmission;
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
 * @return array{0: User, 1: Tenant}
 */
function createCommerceApiUser(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'commerce-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'commerce-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

function createCommerceOrder(Tenant $tenant, array $overrides = []): Order
{
    setCurrentTenant($tenant);

    $order = Order::query()->create([
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

    DB::table('order_items')->insert([
        'order_id' => $order->id,
        'name' => 'منتج تجريبي',
        'qty' => 2,
        'unit_price' => 5000,
        'discount_total' => 0,
        'line_total' => 10000,
        'meta' => json_encode(['type' => 'product']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $order;
}

test('owner can view order detail with lean items and payments', function () {
    [$user, $tenant] = createCommerceApiUser();
    $order = createCommerceOrder($tenant, ['number' => '000321']);

    $this->actingAs($user)
        ->getJson('/api/orders/'.$order->uuid)
        ->assertSuccessful()
        ->assertJsonPath('data.uuid', $order->uuid)
        ->assertJsonPath('data.number', '000321')
        ->assertJsonPath('data.items.0.name', 'منتج تجريبي')
        ->assertJsonPath('data.items.0.qty', 2)
        ->assertJsonPath('data.items.0.is_booking', false)
        ->assertJsonPath('data.items.0.booking', null)
        ->assertJsonMissingPath('data.meta');
});

test('order detail includes booking details for service and unit rental items', function () {
    [$user, $tenant] = createCommerceApiUser();
    setCurrentTenant($tenant);

    $serviceCalendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'أحمد المصور',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $rentalCalendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'غرفة 101',
        'type' => 'rental-unit',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $serviceBooking = Booking::query()->create([
        'tenant_id' => $tenant->id,
        'calendar_id' => $serviceCalendar->id,
        'start_at' => '2026-07-06 09:00:00',
        'end_at' => '2026-07-06 10:00:00',
        'status' => 'pending',
        'price_snapshot' => 150,
        'currency' => 'SAR',
    ]);

    $rentalBooking = Booking::query()->create([
        'tenant_id' => $tenant->id,
        'calendar_id' => $rentalCalendar->id,
        'start_at' => '2026-07-08 00:00:00',
        'end_at' => '2026-07-10 00:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 400,
        'currency' => 'SAR',
    ]);

    $order = Order::query()->create([
        'tenant_id' => $tenant->id,
        'type' => 'order',
        'status' => 'open',
        'channel' => 'manual',
        'number' => '000450',
        'currency_code' => 'SAR',
        'subtotal' => 55000,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => 55000,
        'paid_total' => 0,
        'due_total' => 55000,
        'payment_status' => 'unpaid',
        'issued_at' => now(),
        'financial_status' => 'open',
        'fulfillment_status' => 'unfulfilled',
    ]);

    DB::table('order_items')->insert([
        [
            'order_id' => $order->id,
            'name' => 'خدمة تصوير',
            'qty' => 1,
            'unit_price' => 15000,
            'discount_total' => 0,
            'line_total' => 15000,
            'meta' => json_encode([
                'type' => 'service',
                'booking_id' => $serviceBooking->id,
                'calendar_id' => $serviceCalendar->id,
                'booking_start_at' => '2026-07-06 09:00:00',
                'booking_end_at' => '2026-07-06 10:00:00',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'order_id' => $order->id,
            'name' => 'شقة فاخرة',
            'qty' => 1,
            'unit_price' => 40000,
            'discount_total' => 0,
            'line_total' => 40000,
            'meta' => json_encode([
                'type' => 'unit_rental',
                'booking_id' => $rentalBooking->id,
                'calendar_id' => $rentalCalendar->id,
                'booking_start_at' => '2026-07-08 00:00:00',
                'booking_end_at' => '2026-07-10 00:00:00',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/orders/'.$order->uuid)
        ->assertSuccessful()
        ->assertJsonPath('data.items.0.type', 'service')
        ->assertJsonPath('data.items.0.is_booking', true)
        ->assertJsonPath('data.items.0.booking.calendar_name', 'أحمد المصور')
        ->assertJsonPath('data.items.0.booking.calendar_label', 'مقدم الخدمة')
        ->assertJsonPath('data.items.0.booking.time_label', '09:00 – 10:00')
        ->assertJsonPath('data.items.0.booking.status_label', 'جديد')
        ->assertJsonPath('data.items.0.booking.dates_label', null)
        ->assertJsonPath('data.items.1.type', 'unit_rental')
        ->assertJsonPath('data.items.1.is_booking', true)
        ->assertJsonPath('data.items.1.booking.calendar_name', 'غرفة 101')
        ->assertJsonPath('data.items.1.booking.calendar_label', 'مخزون الوحدات')
        ->assertJsonPath('data.items.1.booking.time_label', null)
        ->assertJsonPath('data.items.1.booking.duration_label', 'ليلتان')
        ->assertJsonPath('data.items.1.booking.status_label', 'مؤكد');

    $datesLabel = $response->json('data.items.1.booking.dates_label');
    expect($datesLabel)->toBeString()->toContain('من')->toContain('إلى');
});

test('foreign tenant cannot view order detail', function () {
    [$user] = createCommerceApiUser();
    [, $otherTenant] = createCommerceApiUser();
    $order = createCommerceOrder($otherTenant);

    $this->actingAs($user)
        ->getJson('/api/orders/'.$order->uuid)
        ->assertNotFound();
});

test('owner can list and view payments', function () {
    [$user, $tenant] = createCommerceApiUser();
    setCurrentTenant($tenant);
    $order = createCommerceOrder($tenant);

    $payment = Payment::query()->create([
        'tenant_id' => $tenant->id,
        'order_id' => $order->id,
        'paymentable_type' => Order::class,
        'paymentable_id' => $order->id,
        'amount' => 5000,
        'currency' => 'SAR',
        'reason' => 'client-buy-from-tenant',
        'gateway' => 'moyasar',
        'source_type' => 'creditcard',
        'initial_status' => 'paid',
        'type' => 'payment',
    ]);

    $this->actingAs($user)
        ->getJson('/api/payments')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $payment->uuid);

    $this->actingAs($user)
        ->getJson('/api/payments/'.$payment->uuid)
        ->assertSuccessful()
        ->assertJsonPath('data.uuid', $payment->uuid)
        ->assertJsonPath('data.order_uuid', $order->uuid)
        ->assertJsonPath('data.amount', 5000);
});

test('owner can list and view invoices', function () {
    [$user, $tenant] = createCommerceApiUser();
    setCurrentTenant($tenant);
    $order = createCommerceOrder($tenant, ['number' => '000444']);

    $invoice = Invoice::query()->create([
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
    ]);

    $this->actingAs($user)
        ->getJson('/api/invoices')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $invoice->uuid);

    $this->actingAs($user)
        ->getJson('/api/invoices/'.$invoice->uuid)
        ->assertSuccessful()
        ->assertJsonPath('data.uuid', $invoice->uuid)
        ->assertJsonPath('data.order_label', 'طلب #000444')
        ->assertJsonPath('data.due', 5000);
});

test('owner can list and view form submissions and mark them read', function () {
    [$user, $tenant] = createCommerceApiUser();
    setCurrentTenant($tenant);

    $form = Content::query()->create([
        'tenant_id' => $tenant->id,
        'uuid' => (string) Str::uuid(),
        'type' => 'forms',
        'title' => 'نموذج تواصل',
        'slug' => 'contact-'.Str::lower(Str::random(4)),
        'status' => 'published',
    ]);

    $submission = FormSubmission::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $form->id,
        'status' => 'new',
        'data' => [
            'fields' => [
                ['id' => '1', 'name' => 'message', 'label' => 'الرسالة', 'type' => 'textarea', 'value' => 'مرحبا'],
            ],
        ],
        'submitted_at' => now(),
        'read_at' => null,
    ]);

    $this->actingAs($user)
        ->getJson('/api/form-submissions')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $submission->id)
        ->assertJsonPath('data.0.unread', true)
        ->assertJsonPath('data.0.form_title', 'نموذج تواصل');

    $this->actingAs($user)
        ->getJson('/api/form-submissions/'.$submission->id)
        ->assertSuccessful()
        ->assertJsonPath('data.id', $submission->id)
        ->assertJsonPath('data.fields.0.value', 'مرحبا')
        ->assertJsonPath('data.unread', false);

    expect($submission->fresh()->read_at)->not->toBeNull()
        ->and($submission->fresh()->status)->toBe('read');
});

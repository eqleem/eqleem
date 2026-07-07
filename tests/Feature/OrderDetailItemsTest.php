<?php

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Order;
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

function createTenantWithUserForOrderDetailItems(): array
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

function createOrderWithItems(Tenant $tenant, array $items): Order
{
    $subtotal = collect($items)->sum('line_total');

    $order = Order::create([
        'tenant_id' => $tenant->id,
        'type' => 'order',
        'status' => 'open',
        'channel' => 'manual',
        'number' => '000100',
        'currency_code' => 'SAR',
        'subtotal' => $subtotal,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => $subtotal,
        'paid_total' => 0,
        'due_total' => $subtotal,
        'payment_status' => 'unpaid',
        'issued_at' => now(),
        'financial_status' => 'draft',
        'fulfillment_status' => 'unfulfilled',
    ]);

    foreach ($items as $item) {
        DB::table('order_items')->insert([
            'order_id' => $order->id,
            'product_id' => $item['product_id'] ?? null,
            'sku' => $item['sku'] ?? null,
            'name' => $item['name'],
            'qty' => $item['qty'],
            'unit_price' => $item['unit_price'],
            'discount_total' => $item['discount_total'] ?? 0,
            'tax_total' => $item['tax_total'] ?? 0,
            'line_total' => $item['line_total'],
            'meta' => json_encode($item['meta'] ?? ['type' => 'product']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return $order;
}

it('shows booking details for service and unit rental items', function () {
    [$user, $tenant] = createTenantWithUserForOrderDetailItems();

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'تقويم الخدمات',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $serviceBooking = Booking::query()->create([
        'tenant_id' => $tenant->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 09:00:00',
        'end_at' => '2026-07-06 10:00:00',
        'status' => 'pending',
        'price_snapshot' => 150,
        'currency' => 'SAR',
    ]);

    $rentalBooking = Booking::query()->create([
        'tenant_id' => $tenant->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-08 00:00:00',
        'end_at' => '2026-07-09 00:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 300,
        'currency' => 'SAR',
    ]);

    $order = createOrderWithItems($tenant, [
        [
            'name' => 'خدمة تصوير',
            'qty' => 1,
            'unit_price' => 15000,
            'line_total' => 15000,
            'meta' => [
                'type' => 'service',
                'booking_id' => $serviceBooking->id,
                'calendar_id' => $calendar->id,
                'booking_start_at' => '2026-07-06 09:00:00',
                'booking_end_at' => '2026-07-06 10:00:00',
            ],
        ],
        [
            'name' => 'شقة فاخرة',
            'qty' => 1,
            'unit_price' => 30000,
            'line_total' => 30000,
            'meta' => [
                'type' => 'unit_rental',
                'booking_id' => $rentalBooking->id,
                'calendar_id' => $calendar->id,
                'booking_start_at' => '2026-07-08 00:00:00',
                'booking_end_at' => '2026-07-09 00:00:00',
            ],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('admin.orders.detail', ['id' => $order->uuid]))
        ->assertSuccessful()
        ->assertSee('العناصر')
        ->assertSee('خدمة تصوير')
        ->assertSee('خدمة')
        ->assertSee('تاريخ الموعد')
        ->assertSee('وقت الموعد')
        ->assertSee('قيد الانتظار')
        ->assertSee('تقويم الخدمات')
        ->assertSee('سعر الحجز')
        ->assertSee('شقة فاخرة')
        ->assertSee('وحدة تأجير')
        ->assertSee('مؤكد')
        ->assertSee('يوم واحد')
        ->assertDontSee('المقاعد:');
});

it('renders structured shipping address on order detail page', function () {
    [$user, $tenant] = createTenantWithUserForOrderDetailItems();

    $order = createOrderWithItems($tenant, [
        [
            'name' => 'منتج يحتاج شحن',
            'qty' => 1,
            'unit_price' => 5000,
            'line_total' => 5000,
            'meta' => [
                'type' => 'product',
            ],
        ],
    ]);

    $order->update([
        'meta' => [
            'shipping_method' => 'custom:test-shipping',
            'shipping_method_label' => 'شحن سريع',
            'shipping_fee' => 2100,
            'shipping_address' => [
                'address' => 'شارع الملك فهد',
                'country' => 'SA',
                'city_id' => '1',
                'neighborhood' => 'حي العليا',
                'country_label' => 'السعودية',
                'city_label' => 'الرياض',
            ],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('admin.orders.detail', ['id' => $order->uuid]))
        ->assertSuccessful()
        ->assertSee('عنوان الشحن')
        ->assertSee('شارع الملك فهد، حي العليا، الرياض، السعودية')
        ->assertSee('شحن سريع');
});

it('shows product and course details for non-booking items', function () {
    [$user, $tenant] = createTenantWithUserForOrderDetailItems();

    $order = createOrderWithItems($tenant, [
        [
            'name' => 'منتج تجريبي',
            'sku' => 'PRD-100',
            'qty' => 3,
            'unit_price' => 5000,
            'line_total' => 15000,
            'meta' => [
                'type' => 'product',
            ],
        ],
        [
            'name' => 'دورة البرمجة',
            'qty' => 2,
            'unit_price' => 20000,
            'line_total' => 40000,
            'meta' => [
                'type' => 'course',
            ],
        ],
        [
            'name' => 'عنصر مخصص',
            'qty' => 1,
            'unit_price' => 1000,
            'line_total' => 1000,
            'meta' => [
                'type' => 'other',
                'description' => 'وصف العنصر المخصص',
            ],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('admin.orders.detail', ['id' => $order->uuid]))
        ->assertSuccessful()
        ->assertSee('منتج تجريبي')
        ->assertSee('منتج')
        ->assertSee('SKU: PRD-100')
        ->assertSee('الكمية: 3')
        ->assertSee('سعر الوحدة')
        ->assertSee('دورة البرمجة')
        ->assertSee('دورة')
        ->assertSee('المقاعد: 2')
        ->assertSee('تسجيل في الدورة')
        ->assertSee('عنصر مخصص')
        ->assertSee('وصف العنصر المخصص')
        ->assertDontSee('تاريخ الموعد')
        ->assertDontSee('وقت الموعد');
});

<?php

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createOrderBookingApiUser(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'order-booking-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'order-booking-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

/**
 * @return array{0: Content, 1: Calendar}
 */
function createOrderBookingService(Tenant $tenant): array
{
    Carbon::setTestNow('2026-07-06 08:00:00');

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'مقدم الخدمة',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $service = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('services'),
        'title' => 'خدمة تصوير',
        'slug' => 'photo-service-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'active' => true,
        'price' => 15000,
        'data' => [
            'price' => 15000,
            'duration_minutes' => 60,
        ],
    ]);

    $service->calendars()->attach($calendar->id, ['type' => 'service-provider', 'active' => true]);

    return [$service, $calendar];
}

/**
 * @return array{0: Content, 1: Calendar}
 */
function createOrderBookingUnit(Tenant $tenant): array
{
    Carbon::setTestNow('2026-07-06 08:00:00');

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'غرفة 101',
        'type' => 'rental-unit',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $unit = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('unit-rental'),
        'title' => 'شقة فاخرة',
        'slug' => 'unit-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'active' => true,
        'price' => 20000,
        'data' => [
            'price' => 20000,
            'duration_minutes' => 60,
        ],
    ]);

    $unit->calendars()->attach($calendar->id, ['type' => 'rental-unit', 'active' => true]);

    return [$unit, $calendar];
}

test('owner can create an order with a service booking item', function () {
    [$user, $tenant] = createOrderBookingApiUser();
    [$service, $calendar] = createOrderBookingService($tenant);

    $this->actingAs($user)
        ->postJson('/api/orders', [
            'items' => [
                [
                    'type' => 'service',
                    'name' => $service->title,
                    'product_id' => $service->id,
                    'qty' => 1,
                    'unit_price' => 150,
                    'calendar_id' => $calendar->id,
                    'booking_start_at' => '2026-07-06 09:00:00',
                    'booking_end_at' => '2026-07-06 10:00:00',
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.items.0.name', $service->title);

    expect(Order::query()->count())->toBe(1)
        ->and(Booking::query()->count())->toBe(1);

    $booking = Booking::query()->first();
    $orderItem = DB::table('order_items')->first();
    $meta = json_decode($orderItem->meta, true);

    expect($booking->content_id)->toBe($service->id)
        ->and($booking->calendar_id)->toBe($calendar->id)
        ->and($booking->start_at->format('Y-m-d H:i:s'))->toBe('2026-07-06 09:00:00')
        ->and($booking->order_id)->toBe(Order::query()->value('id'))
        ->and($orderItem->booking_id)->toBe($booking->id)
        ->and($meta['booking_id'])->toBe($booking->id)
        ->and($meta['type'])->toBe('service');
});

test('owner can create an order with a unit rental date range', function () {
    [$user, $tenant] = createOrderBookingApiUser();
    [$unit, $calendar] = createOrderBookingUnit($tenant);

    $this->actingAs($user)
        ->postJson('/api/orders', [
            'items' => [
                [
                    'type' => 'unit_rental',
                    'name' => $unit->title,
                    'product_id' => $unit->id,
                    'qty' => 1,
                    'unit_price' => 400,
                    'calendar_id' => $calendar->id,
                    'booking_start_at' => '2026-07-06 00:00:00',
                    'booking_end_at' => '2026-07-08 00:00:00',
                ],
            ],
        ])
        ->assertSuccessful();

    expect(Booking::query()->count())->toBe(1);

    $booking = Booking::query()->first();

    expect($booking->start_at->format('Y-m-d H:i:s'))->toBe('2026-07-06 00:00:00')
        ->and($booking->end_at->format('Y-m-d H:i:s'))->toBe('2026-07-08 00:00:00')
        ->and((float) $booking->price_snapshot)->toBe(400.0);
});

test('service booking fields are required on order create', function () {
    [$user, $tenant] = createOrderBookingApiUser();
    [$service] = createOrderBookingService($tenant);

    $this->actingAs($user)
        ->postJson('/api/orders', [
            'items' => [
                [
                    'type' => 'service',
                    'name' => $service->title,
                    'product_id' => $service->id,
                    'qty' => 1,
                    'unit_price' => 150,
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.calendar_id']);
});

test('cannot double-book the same service slot in one order', function () {
    [$user, $tenant] = createOrderBookingApiUser();
    [$service, $calendar] = createOrderBookingService($tenant);

    $this->actingAs($user)
        ->postJson('/api/orders', [
            'items' => [
                [
                    'type' => 'service',
                    'name' => $service->title,
                    'product_id' => $service->id,
                    'qty' => 1,
                    'unit_price' => 150,
                    'calendar_id' => $calendar->id,
                    'booking_start_at' => '2026-07-06 09:00:00',
                    'booking_end_at' => '2026-07-06 10:00:00',
                ],
                [
                    'type' => 'service',
                    'name' => $service->title,
                    'product_id' => $service->id,
                    'qty' => 1,
                    'unit_price' => 150,
                    'calendar_id' => $calendar->id,
                    'booking_start_at' => '2026-07-06 09:00:00',
                    'booking_end_at' => '2026-07-06 10:00:00',
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['items.1.booking_start_at']);

    expect(Order::query()->count())->toBe(0)
        ->and(Booking::query()->count())->toBe(0);
});

test('cannot book an already reserved unit rental range', function () {
    [$user, $tenant] = createOrderBookingApiUser();
    [$unit, $calendar] = createOrderBookingUnit($tenant);

    setCurrentTenant($tenant);

    Booking::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $unit->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-07 00:00:00',
        'end_at' => '2026-07-09 00:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 400,
        'currency' => 'SAR',
    ]);

    $this->actingAs($user)
        ->postJson('/api/orders', [
            'items' => [
                [
                    'type' => 'unit_rental',
                    'name' => $unit->title,
                    'product_id' => $unit->id,
                    'qty' => 1,
                    'unit_price' => 400,
                    'calendar_id' => $calendar->id,
                    'booking_start_at' => '2026-07-06 00:00:00',
                    'booking_end_at' => '2026-07-08 00:00:00',
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['items.0.booking_start_at']);
});

test('availability returns booked ranges for unit calendars', function () {
    [$user, $tenant] = createOrderBookingApiUser();
    [$unit, $calendar] = createOrderBookingUnit($tenant);

    setCurrentTenant($tenant);

    Booking::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $unit->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-07 00:00:00',
        'end_at' => '2026-07-09 00:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 400,
        'currency' => 'SAR',
    ]);

    $this->actingAs($user)
        ->getJson('/api/bookings/availability?content_id='.$unit->id.'&calendar_id='.$calendar->id)
        ->assertSuccessful()
        ->assertJsonPath('data.booked_ranges.0.start_date', '2026-07-07')
        ->assertJsonPath('data.booked_ranges.0.end_date', '2026-07-09');
});

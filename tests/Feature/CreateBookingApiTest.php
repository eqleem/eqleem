<?php

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Client;
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
function createBookingApiUser(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'create-booking-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'create-booking-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

function createBookingApiClient(Tenant $tenant): Client
{
    setCurrentTenant($tenant);

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'محمد العتيبي',
        'email' => 'client-'.Str::lower(Str::random(6)).'@example.com',
        'phone' => '05'.random_int(10000000, 99999999),
        'tenant_id' => $tenant->id,
        'active' => true,
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

/**
 * @return array{0: Content, 1: Calendar}
 */
function createBookingApiService(Tenant $tenant): array
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

test('guests cannot create bookings', function () {
    $this->postJson('/api/bookings', [])
        ->assertUnauthorized();
});

test('owner can create a service booking with linked order', function () {
    [$user, $tenant] = createBookingApiUser();
    $client = createBookingApiClient($tenant);
    [$service, $calendar] = createBookingApiService($tenant);

    $this->actingAs($user)
        ->postJson('/api/bookings', [
            'client_id' => $client->id,
            'type' => 'service',
            'content_id' => $service->id,
            'calendar_id' => $calendar->id,
            'start_at' => '2026-07-06 09:00:00',
            'end_at' => '2026-07-06 10:00:00',
            'status' => 'confirmed',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.content.title', 'خدمة تصوير')
        ->assertJsonPath('data.client', 'محمد العتيبي')
        ->assertJsonPath('data.calendar.name', 'مقدم الخدمة')
        ->assertJsonPath('message', 'تم إنشاء الحجز بنجاح.');

    expect(Booking::query()->count())->toBe(1)
        ->and(Order::query()->count())->toBe(1);

    $booking = Booking::query()->first();
    $orderItem = DB::table('order_items')->first();

    expect($booking->content_id)->toBe($service->id)
        ->and($booking->calendar_id)->toBe($calendar->id)
        ->and($booking->status)->toBe('confirmed')
        ->and(json_decode($orderItem->meta, true)['booking_id'])->toBe($booking->id);
});

test('availability endpoint returns calendars dates and slots', function () {
    [$user, $tenant] = createBookingApiUser();
    [$service, $calendar] = createBookingApiService($tenant);

    $this->actingAs($user)
        ->getJson('/api/bookings/availability?content_id='.$service->id)
        ->assertSuccessful()
        ->assertJsonPath('data.content.title', 'خدمة تصوير')
        ->assertJsonCount(1, 'data.calendars')
        ->assertJsonPath('data.calendars.0.id', $calendar->id);

    $this->actingAs($user)
        ->getJson('/api/bookings/availability?content_id='.$service->id.'&calendar_id='.$calendar->id.'&date=2026-07-06')
        ->assertSuccessful()
        ->assertJsonPath('data.available_dates.0', '2026-07-06')
        ->assertJsonFragment(['start_at' => '2026-07-06 09:00:00', 'available' => true]);
});

test('cannot double-book the same service slot', function () {
    [$user, $tenant] = createBookingApiUser();
    [$service, $calendar] = createBookingApiService($tenant);

    setCurrentTenant($tenant);

    Booking::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 09:00:00',
        'end_at' => '2026-07-06 10:00:00',
        'status' => 'pending',
        'price_snapshot' => 150,
        'currency' => 'SAR',
    ]);

    $this->actingAs($user)
        ->postJson('/api/bookings', [
            'type' => 'service',
            'content_id' => $service->id,
            'calendar_id' => $calendar->id,
            'start_at' => '2026-07-06 09:00:00',
            'end_at' => '2026-07-06 10:00:00',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_at']);
});

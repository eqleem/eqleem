<?php

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Client;
use App\Models\Content;
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
function createBookingsApiUser(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'email' => 'bookings-owner-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'bookings-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

function createBookingsApiClient(Tenant $tenant, array $overrides = []): Client
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

/**
 * @return array{0: Content, 1: Calendar}
 */
function createBookingsApiServiceWithCalendar(Tenant $tenant): array
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

test('guests cannot list bookings', function () {
    $this->getJson('/api/bookings')
        ->assertUnauthorized();
});

test('inactive tenant cannot list bookings', function () {
    [$user] = createBookingsApiUser(['active' => false]);

    $this->actingAs($user)
        ->getJson('/api/bookings')
        ->assertForbidden();
});

test('owner lists only current tenant bookings with lean payload', function () {
    [$user, $tenant] = createBookingsApiUser();
    $client = createBookingsApiClient($tenant);
    [$service, $calendar] = createBookingsApiServiceWithCalendar($tenant);

    setCurrentTenant($tenant);

    $orderId = DB::table('orders')->insertGetId([
        'tenant_id' => $tenant->id,
        'uuid' => (string) Str::uuid(),
        'type' => 'order',
        'status' => 'draft',
        'channel' => 'manual',
        'number' => '000777',
        'currency_code' => 'SAR',
        'subtotal' => 15000,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => 15000,
        'paid_total' => 0,
        'due_total' => 15000,
        'payment_status' => 'unpaid',
        'issued_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $booking = Booking::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'order_id' => $orderId,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 09:00:00',
        'end_at' => '2026-07-06 10:00:00',
        'status' => 'new',
        'price_snapshot' => 150,
        'currency' => 'SAR',
    ]);

    DB::table('order_items')->insert([
        'order_id' => $orderId,
        'product_id' => $service->id,
        'booking_id' => $booking->id,
        'name' => $service->title,
        'qty' => 1,
        'unit_price' => 15000,
        'discount_total' => 0,
        'tax_total' => 0,
        'line_total' => 15000,
        'meta' => json_encode([
            'type' => 'service',
            'booking_id' => $booking->id,
            'calendar_id' => $calendar->id,
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    [$otherUser, $otherTenant] = createBookingsApiUser();
    setCurrentTenant($otherTenant);

    Booking::query()->create([
        'tenant_id' => $otherTenant->id,
        'start_at' => '2026-07-06 11:00:00',
        'end_at' => '2026-07-06 12:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 99,
        'currency' => 'SAR',
    ]);

    $this->actingAs($user)
        ->getJson('/api/bookings')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $booking->id)
        ->assertJsonPath('data.0.status', 'new')
        ->assertJsonPath('data.0.status_label', 'جديد')
        ->assertJsonPath('data.0.client.name', 'محمد العتيبي')
        ->assertJsonPath('data.0.content.title', 'خدمة تصوير')
        ->assertJsonPath('data.0.content.type', 'service')
        ->assertJsonPath('data.0.calendar.name', 'مقدم الخدمة')
        ->assertJsonPath('data.0.order.number', '000777')
        ->assertJsonMissingPath('data.0.meta')
        ->assertJsonMissingPath('data.0.data');
});

test('status filter returns only matching booking statuses', function () {
    [$user, $tenant] = createBookingsApiUser();
    $client = createBookingsApiClient($tenant);
    [$service, $calendar] = createBookingsApiServiceWithCalendar($tenant);

    setCurrentTenant($tenant);

    $confirmed = Booking::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 09:00:00',
        'end_at' => '2026-07-06 10:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 150,
        'currency' => 'SAR',
    ]);

    Booking::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-07 09:00:00',
        'end_at' => '2026-07-07 10:00:00',
        'status' => 'new',
        'price_snapshot' => 90,
        'currency' => 'SAR',
    ]);

    $this->actingAs($user)
        ->getJson('/api/bookings?'.http_build_query(['status' => 'confirmed']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $confirmed->id)
        ->assertJsonPath('data.0.status', 'confirmed');

    $this->actingAs($user)
        ->getJson('/api/bookings?'.http_build_query(['status' => 'processing']))
        ->assertUnprocessable();
});

test('status filter new includes legacy pending bookings', function () {
    [$user, $tenant] = createBookingsApiUser();
    $client = createBookingsApiClient($tenant);
    [$service, $calendar] = createBookingsApiServiceWithCalendar($tenant);

    setCurrentTenant($tenant);

    $pending = Booking::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 09:00:00',
        'end_at' => '2026-07-06 10:00:00',
        'status' => 'pending',
        'price_snapshot' => 150,
        'currency' => 'SAR',
    ]);

    Booking::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-07 09:00:00',
        'end_at' => '2026-07-07 10:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 90,
        'currency' => 'SAR',
    ]);

    $this->actingAs($user)
        ->getJson('/api/bookings?'.http_build_query(['status' => 'new']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $pending->id)
        ->assertJsonPath('data.0.status', 'new')
        ->assertJsonPath('data.0.status_label', 'جديد');
});

test('search filters bookings by client and content title', function () {
    [$user, $tenant] = createBookingsApiUser();
    $client = createBookingsApiClient($tenant, ['name' => 'سارة القحطاني']);
    [$service, $calendar] = createBookingsApiServiceWithCalendar($tenant);

    setCurrentTenant($tenant);

    Booking::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 09:00:00',
        'end_at' => '2026-07-06 10:00:00',
        'status' => 'new',
        'price_snapshot' => 150,
        'currency' => 'SAR',
    ]);

    Booking::query()->create([
        'tenant_id' => $tenant->id,
        'start_at' => '2026-07-07 09:00:00',
        'end_at' => '2026-07-07 10:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 90,
        'currency' => 'SAR',
    ]);

    $this->actingAs($user)
        ->getJson('/api/bookings?'.http_build_query(['search' => 'سارة']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.client.name', 'سارة القحطاني');

    $this->actingAs($user)
        ->getJson('/api/bookings?'.http_build_query(['search' => 'تصوير']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.content.title', 'خدمة تصوير');
});

test('date range filter returns overlapping bookings for calendar view', function () {
    [$user, $tenant] = createBookingsApiUser();
    $client = createBookingsApiClient($tenant);
    [$service, $calendar] = createBookingsApiServiceWithCalendar($tenant);

    setCurrentTenant($tenant);

    $inside = Booking::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-10 09:00:00',
        'end_at' => '2026-07-10 10:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 150,
        'currency' => 'SAR',
    ]);

    Booking::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-08-02 09:00:00',
        'end_at' => '2026-08-02 10:00:00',
        'status' => 'new',
        'price_snapshot' => 90,
        'currency' => 'SAR',
    ]);

    $multiDay = Booking::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-06-28 14:00:00',
        'end_at' => '2026-07-03 11:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 400,
        'currency' => 'SAR',
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/bookings?'.http_build_query([
            'from' => '2026-07-01',
            'to' => '2026-07-31T23:59:59',
            'per_page' => 200,
        ]))
        ->assertSuccessful();

    $ids = collect($response->json('data'))->pluck('id')->all();

    expect($ids)->toContain($inside->id)
        ->and($ids)->toContain($multiDay->id)
        ->and($ids)->toHaveCount(2);

    $this->actingAs($user)
        ->getJson('/api/bookings?'.http_build_query([
            'from' => '2026-08-01',
            'to' => '2026-07-01',
        ]))
        ->assertUnprocessable();
});

test('guests cannot view a booking', function () {
    $this->getJson('/api/bookings/1')
        ->assertUnauthorized();
});

test('owner can view a booking with full detail payload', function () {
    [$user, $tenant] = createBookingsApiUser();
    $client = createBookingsApiClient($tenant);
    [$service, $calendar] = createBookingsApiServiceWithCalendar($tenant);

    setCurrentTenant($tenant);

    $orderUuid = (string) Str::uuid();

    $orderId = DB::table('orders')->insertGetId([
        'tenant_id' => $tenant->id,
        'uuid' => $orderUuid,
        'type' => 'order',
        'status' => 'draft',
        'channel' => 'manual',
        'number' => '000888',
        'currency_code' => 'SAR',
        'subtotal' => 15000,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => 15000,
        'paid_total' => 0,
        'due_total' => 15000,
        'payment_status' => 'unpaid',
        'issued_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $booking = Booking::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'order_id' => $orderId,
        'content_id' => $service->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 09:00:00',
        'end_at' => '2026-07-06 10:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 150,
        'currency' => 'SAR',
    ]);

    DB::table('order_items')->insert([
        'order_id' => $orderId,
        'product_id' => $service->id,
        'booking_id' => $booking->id,
        'name' => $service->title,
        'qty' => 1,
        'unit_price' => 15000,
        'discount_total' => 0,
        'tax_total' => 0,
        'line_total' => 15000,
        'meta' => json_encode([
            'type' => 'service',
            'booking_id' => $booking->id,
            'calendar_id' => $calendar->id,
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->getJson('/api/bookings/'.$booking->id)
        ->assertSuccessful()
        ->assertJsonPath('data.id', $booking->id)
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('data.status_label', 'مؤكد')
        ->assertJsonPath('data.client.name', 'محمد العتيبي')
        ->assertJsonPath('data.client.uuid', $client->uuid)
        ->assertJsonPath('data.content.title', 'خدمة تصوير')
        ->assertJsonPath('data.content.type', 'service')
        ->assertJsonPath('data.calendar.name', 'مقدم الخدمة')
        ->assertJsonPath('data.time_label', '09:00 – 10:00')
        ->assertJsonPath('data.order.uuid', $orderUuid)
        ->assertJsonPath('data.order.number', '000888')
        ->assertJsonMissingPath('data.meta')
        ->assertJsonMissingPath('data.data');
});

test('owner cannot view booking from another tenant', function () {
    [$user] = createBookingsApiUser();
    [$otherUser, $otherTenant] = createBookingsApiUser();

    setCurrentTenant($otherTenant);

    $booking = Booking::query()->create([
        'tenant_id' => $otherTenant->id,
        'start_at' => '2026-07-06 11:00:00',
        'end_at' => '2026-07-06 12:00:00',
        'status' => 'confirmed',
        'price_snapshot' => 99,
        'currency' => 'SAR',
    ]);

    $this->actingAs($user)
        ->getJson('/api/bookings/'.$booking->id)
        ->assertNotFound();
});

test('show booking returns not found for missing id', function () {
    [$user] = createBookingsApiUser();

    $this->actingAs($user)
        ->getJson('/api/bookings/999999')
        ->assertNotFound();
});

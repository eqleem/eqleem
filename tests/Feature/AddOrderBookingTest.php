<?php

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);
});

function createTenantWithUserForAddOrder(): array
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

function createServiceWithCalendar(Tenant $tenant): array
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
        'slug' => 'photo-service',
        'status' => 'published',
        'active' => true,
        'data' => [
            'price' => 15000,
            'duration_minutes' => 60,
        ],
    ]);

    $service->calendars()->attach($calendar->id, ['type' => 'service-provider', 'active' => true]);

    return [$service, $calendar];
}

it('loads booking fields when selecting a service product', function () {
    [$user, $tenant] = createTenantWithUserForAddOrder();
    [$service, $calendar] = createServiceWithCalendar($tenant);

    Livewire::actingAs($user)
        ->test('admin::orders.add-order')
        ->call('addItem', 'service')
        ->call('selectProduct', 0, [
            'product_id' => $service->id,
            'name' => $service->title,
            'unit_price' => 15000,
            'duration_minutes' => 60,
        ])
        ->assertSet('items.0.calendar_id', $calendar->id)
        ->assertSet('items.0.qty', 1)
        ->assertNotSet('items.0.available_dates', []);
});

it('creates an order with a booking for a service item', function () {
    [$user, $tenant] = createTenantWithUserForAddOrder();
    [$service, $calendar] = createServiceWithCalendar($tenant);

    Livewire::actingAs($user)
        ->test('admin::orders.add-order')
        ->call('addItem', 'service')
        ->call('selectProduct', 0, [
            'product_id' => $service->id,
            'name' => $service->title,
            'unit_price' => 15000,
            'duration_minutes' => 60,
        ])
        ->set('items.0.booking_date', '2026-07-06')
        ->call('loadBookingTimeSlots', 0)
        ->call('selectTimeSlot', 0, '2026-07-06 09:00:00', '2026-07-06 10:00:00')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Order::query()->count())->toBe(1)
        ->and(Booking::query()->count())->toBe(1);

    $booking = Booking::query()->first();

    expect($booking->content_id)->toBe($service->id)
        ->and($booking->calendar_id)->toBe($calendar->id)
        ->and($booking->start_at->format('Y-m-d H:i:s'))->toBe('2026-07-06 09:00:00');

    $orderItem = DB::table('order_items')->first();

    expect(json_decode($orderItem->meta, true)['booking_id'])->toBe($booking->id)
        ->and(json_decode($orderItem->meta, true)['type'])->toBe('service');
});

it('requires booking date and time for service items', function () {
    [$user, $tenant] = createTenantWithUserForAddOrder();
    [$service] = createServiceWithCalendar($tenant);

    Livewire::actingAs($user)
        ->test('admin::orders.add-order')
        ->call('addItem', 'service')
        ->call('selectProduct', 0, [
            'product_id' => $service->id,
            'name' => $service->title,
            'unit_price' => 15000,
            'duration_minutes' => 60,
        ])
        ->call('submit')
        ->assertHasErrors(['items.0.booking_date', 'items.0.booking_time']);
});

it('still uses quantity flow for product items', function () {
    [$user, $tenant] = createTenantWithUserForAddOrder();

    $product = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج تجريبي',
        'slug' => 'test-product',
        'status' => 'published',
        'active' => true,
        'data' => ['price' => 5000],
    ]);

    Livewire::actingAs($user)
        ->test('admin::orders.add-order')
        ->call('addItem', 'product')
        ->call('selectProduct', 0, [
            'product_id' => $product->id,
            'name' => $product->title,
            'unit_price' => 5000,
            'duration_minutes' => 60,
        ])
        ->set('items.0.qty', 2)
        ->call('submit')
        ->assertHasNoErrors();

    $orderItem = DB::table('order_items')->first();

    expect($orderItem->qty)->toBe(2)
        ->and(json_decode($orderItem->meta, true)['type'])->toBe('product')
        ->and(Booking::query()->count())->toBe(0);
});

it('uses quantity flow for menu items', function () {
    [$user, $tenant] = createTenantWithUserForAddOrder();

    $menuItem = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('menu'),
        'title' => 'برجر لحم',
        'slug' => 'beef-burger',
        'status' => 'published',
        'active' => true,
        'data' => ['price' => 3500],
    ]);

    Livewire::actingAs($user)
        ->test('admin::orders.add-order')
        ->call('addItem', 'menu')
        ->call('selectProduct', 0, [
            'product_id' => $menuItem->id,
            'name' => $menuItem->title,
            'unit_price' => 3500,
            'duration_minutes' => 60,
        ])
        ->set('items.0.qty', 3)
        ->call('submit')
        ->assertHasNoErrors();

    $orderItem = DB::table('order_items')->first();

    expect($orderItem->qty)->toBe(3)
        ->and(json_decode($orderItem->meta, true)['type'])->toBe('menu')
        ->and(Booking::query()->count())->toBe(0);
});

it('exposes new quantity item types in add item options', function () {
    [$user] = createTenantWithUserForAddOrder();

    Livewire::actingAs($user)
        ->test('admin::orders.add-order')
        ->assertViewHas('addItemTypeOptions', fn (array $options): bool => isset(
            $options['digital_product'],
            $options['digital_service'],
            $options['menu'],
        ));
});

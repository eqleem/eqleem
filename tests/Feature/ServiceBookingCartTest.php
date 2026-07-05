<?php

use App\Livewire\Tenant\Courses\Detail as CourseDetail;
use App\Livewire\Tenant\Pages\Checkout;
use App\Livewire\Tenant\Pages\OrderConfirmation;
use App\Livewire\Tenant\Services\Detail as ServiceDetail;
use App\Livewire\Tenant\Store\Detail as StoreDetail;
use App\Models\Booking;
use App\Models\Calendar;
use App\Models\CartItem;
use App\Models\Content;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);

    view()->prependNamespace('tenant-theme', public_path('themes/default'));
    view()->prependNamespace('default-tenant-theme', public_path('themes/default'));

    Carbon::setTestNow('2026-07-06 08:00:00');
});

/**
 * @return array{tenant: Tenant, service: Content, calendar: Calendar}
 */
function createServiceBookingContext(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Service Tenant',
        'handle' => 'service-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'مقدم الخدمة',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $service = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('services'),
        'title' => 'خدمة تصوير',
        'slug' => 'photo-service',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => [
            'price' => 15000,
            'duration_minutes' => 60,
        ],
    ]);

    $service->calendars()->attach($calendar->id, ['type' => 'service-provider', 'active' => true]);

    return compact('tenant', 'service', 'calendar');
}

it('adds a service booking to the cart after selecting date and time', function () {
    ['tenant' => $tenant, 'service' => $service, 'calendar' => $calendar] = createServiceBookingContext();

    setCurrentTenant($tenant);

    Livewire::test(ServiceDetail::class, ['slug' => $service->slug])
        ->call('openBookingModal')
        ->assertSet('calendarId', $calendar->id)
        ->assertNotSet('availableDates', [])
        ->set('bookingDate', '2026-07-06')
        ->call('selectTimeSlot', '2026-07-06 09:00:00', '2026-07-06 10:00:00')
        ->call('addToCart')
        ->assertSet('addedToCart', true);

    $item = CartItem::query()->firstOrFail();

    expect(CartItem::query()->count())->toBe(1)
        ->and($item->itemType())->toBe('service')
        ->and($item->isBooking())->toBeTrue()
        ->and($item->calendarId())->toBe($calendar->id)
        ->and($item->bookingStartAt())->toBe('2026-07-06 09:00:00')
        ->and($item->bookingEndAt())->toBe('2026-07-06 10:00:00');
});

it('creates a mixed ecommerce order with product service and course items', function () {
    ['tenant' => $tenant, 'service' => $service, 'calendar' => $calendar] = createServiceBookingContext();

    setCurrentTenant($tenant);

    $product = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('store'),
        'title' => 'منتج تجريبي',
        'slug' => 'demo-product',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 25000],
    ]);

    $course = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('courses'),
        'title' => 'دورة برمجة',
        'slug' => 'programming-course',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 9900],
    ]);

    Setting::savePaymentMethod('cash-on-delivery', [
        'label' => 'الدفع عند الاستلام',
    ], true);

    Livewire::test(StoreDetail::class, ['slug' => $product->slug])
        ->call('addToCart');

    Livewire::test(ServiceDetail::class, ['slug' => $service->slug])
        ->set('calendarId', $calendar->id)
        ->set('bookingDate', '2026-07-06')
        ->call('selectTimeSlot', '2026-07-06 09:00:00', '2026-07-06 10:00:00')
        ->call('addToCart');

    Livewire::test(CourseDetail::class, ['slug' => $course->slug])
        ->call('addToCart');

    Livewire::test(Checkout::class)
        ->set('name', 'أحمد محمد')
        ->set('phone', '0500000000')
        ->set('shippingMethod', 'pickup')
        ->set('paymentMethod', 'cash-on-delivery')
        ->call('confirmCashOnDelivery');

    $order = Order::query()->firstOrFail();

    expect(CartItem::query()->count())->toBe(0)
        ->and($order->grand_total)->toBe(49900)
        ->and(DB::table('order_items')->where('order_id', $order->id)->count())->toBe(3)
        ->and(Booking::query()->count())->toBe(1);

    $booking = Booking::query()->firstOrFail();

    expect($booking->content_id)->toBe($service->id)
        ->and($booking->calendar_id)->toBe($calendar->id)
        ->and($booking->start_at->format('Y-m-d H:i:s'))->toBe('2026-07-06 09:00:00');

    $serviceOrderItemMeta = json_decode((string) DB::table('order_items')
        ->where('order_id', $order->id)
        ->where('name', $service->title)
        ->value('meta'), true);

    expect($serviceOrderItemMeta['type'])->toBe('service')
        ->and($serviceOrderItemMeta['booking_id'])->toBe($booking->id);

    session(['recent_order_id' => $order->id]);

    Livewire::test(OrderConfirmation::class, ['order' => $order])
        ->assertSee('تفاصيل الحجز')
        ->assertSee('مقدم الخدمة')
        ->assertSee($calendar->name)
        ->assertSee('التاريخ')
        ->assertSee('الوقت');
});

it('marks a reserved cart slot as unavailable for another service booking attempt', function () {
    ['tenant' => $tenant, 'service' => $service, 'calendar' => $calendar] = createServiceBookingContext();

    setCurrentTenant($tenant);

    Livewire::test(ServiceDetail::class, ['slug' => $service->slug])
        ->set('calendarId', $calendar->id)
        ->set('bookingDate', '2026-07-06')
        ->call('selectTimeSlot', '2026-07-06 09:00:00', '2026-07-06 10:00:00')
        ->call('addToCart');

    Livewire::test(ServiceDetail::class, ['slug' => $service->slug])
        ->set('calendarId', $calendar->id)
        ->set('bookingDate', '2026-07-06')
        ->assertSet('timeSlots', fn (array $slots): bool => collect($slots)
            ->firstWhere('start', '09:00')['available'] === false);
});

it('requires booking date and time before adding a service to the cart', function () {
    ['tenant' => $tenant, 'service' => $service] = createServiceBookingContext();

    setCurrentTenant($tenant);

    Livewire::test(ServiceDetail::class, ['slug' => $service->slug])
        ->call('addToCart')
        ->assertHasErrors(['bookingDate', 'bookingStartAt']);
});

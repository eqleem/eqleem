<?php

use App\Livewire\Tenant\DigitalProducts\Detail as DigitalProductDetail;
use App\Livewire\Tenant\Menu\Index as MenuIndex;
use App\Livewire\Tenant\Pages\Checkout;
use App\Livewire\Tenant\PropertiesRental\Detail as UnitRentalDetail;
use App\Models\Calendar;
use App\Models\CartItem;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CartService;
use Carbon\Carbon;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
 * @return array{tenant: Tenant}
 */
function createPurchasableCartTenant(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Purchasable Tenant',
        'handle' => 'purchase-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    return compact('tenant');
}

it('adds a menu item with options to the cart', function () {
    ['tenant' => $tenant] = createPurchasableCartTenant();

    $meal = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('menu'),
        'title' => 'برجر لحم',
        'slug' => 'beef-burger',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => [
            'price' => 3500,
            'meal_options' => [[
                'id' => 'size-group',
                'name' => 'الحجم',
                'type' => 'single',
                'required' => true,
                'choices' => [[
                    'id' => 'large',
                    'name' => 'كبير',
                    'price' => 500,
                ]],
            ]],
        ],
    ]);

    Livewire::test(MenuIndex::class)
        ->call('addMealToCart', $meal->id, 2, ['0' => 'large'])
        ->assertSet('addedToCart', true);

    $item = CartItem::query()->firstOrFail();

    expect(CartItem::query()->count())->toBe(1)
        ->and($item->itemType())->toBe('menu')
        ->and($item->quantity)->toBe(2)
        ->and($item->unit_price)->toBe(4000)
        ->and($item->mealOptionsLabel())->toContain('كبير');
});

it('adds a digital product to the cart from its detail page', function () {
    ['tenant' => $tenant] = createPurchasableCartTenant();

    $product = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('digital-products'),
        'title' => 'قالب تصميم',
        'slug' => 'design-template',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 9900],
    ]);

    Livewire::test(DigitalProductDetail::class, ['slug' => $product->slug])
        ->set('quantity', 2)
        ->call('addToCart')
        ->assertSet('addedToCart', true);

    expect(CartItem::query()->value('quantity'))->toBe(2)
        ->and(CartItem::query()->value('unit_price'))->toBe(9900)
        ->and(CartItem::query()->first()->itemType())->toBe('digital_product');
});

it('adds a unit rental booking to the cart', function () {
    ['tenant' => $tenant] = createPurchasableCartTenant();

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'وحدة A',
        'type' => 'unit',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $unit = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('unit-rental'),
        'title' => 'شقة فاخرة',
        'slug' => 'luxury-apartment',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 25000],
    ]);

    $unit->calendars()->attach($calendar->id, ['type' => 'unit', 'active' => true]);

    Livewire::test(UnitRentalDetail::class, ['slug' => $unit->slug])
        ->set('checkIn', '2026-07-06')
        ->set('checkOut', '2026-07-08')
        ->call('addToCart')
        ->assertSet('addedToCart', true);

    $item = CartItem::query()->firstOrFail();

    expect($item->itemType())->toBe('unit_rental')
        ->and($item->isBooking())->toBeTrue()
        ->and($item->unit_price)->toBe(50000)
        ->and(data_get($item->meta, 'nights'))->toBe(2);
});

it('shows unit rental booking dates in checkout summary', function () {
    ['tenant' => $tenant] = createPurchasableCartTenant();

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'وحدة A',
        'type' => 'unit',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $unit = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('unit-rental'),
        'title' => 'شقة فاخرة',
        'slug' => 'luxury-apartment',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 25000],
    ]);

    $unit->calendars()->attach($calendar->id, ['type' => 'unit', 'active' => true]);

    Livewire::test(UnitRentalDetail::class, ['slug' => $unit->slug])
        ->set('checkIn', '2026-07-06')
        ->set('checkOut', '2026-07-08')
        ->call('addToCart');

    Livewire::test(Checkout::class)
        ->assertSee('شقة فاخرة')
        ->assertSee('6 يوليو 2026')
        ->assertSee('8 يوليو 2026');
});

it('allows multiple unit rental bookings with different dates in the cart', function () {
    ['tenant' => $tenant] = createPurchasableCartTenant();

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'وحدة A',
        'type' => 'unit',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $unit = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('unit-rental'),
        'title' => 'شقة فاخرة',
        'slug' => 'luxury-apartment',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 25000],
    ]);

    $unit->calendars()->attach($calendar->id, ['type' => 'unit', 'active' => true]);

    $cart = app(CartService::class);

    $cart->addBooking($unit, [
        'calendar_id' => $calendar->id,
        'calendar_name' => $calendar->name,
        'booking_date' => '2026-07-06',
        'booking_start_at' => '2026-07-06 00:00:00',
        'booking_end_at' => '2026-07-08 00:00:00',
        'duration_minutes' => 0,
        'unit_price' => 50000,
        'nights' => 2,
        'check_in' => '2026-07-06',
        'check_out' => '2026-07-08',
    ]);

    $cart->addBooking($unit, [
        'calendar_id' => $calendar->id,
        'calendar_name' => $calendar->name,
        'booking_date' => '2026-07-10',
        'booking_start_at' => '2026-07-10 00:00:00',
        'booking_end_at' => '2026-07-12 00:00:00',
        'duration_minutes' => 0,
        'unit_price' => 50000,
        'nights' => 2,
        'check_in' => '2026-07-10',
        'check_out' => '2026-07-12',
    ]);

    expect(CartItem::query()->count())->toBe(2);

    $signatures = CartItem::query()->orderBy('id')->pluck('line_signature')->all();

    expect($signatures)->toBe([
        '2026-07-06 00:00:00|2026-07-08 00:00:00',
        '2026-07-10 00:00:00|2026-07-12 00:00:00',
    ]);
});

it('does not duplicate the same unit rental booking in the cart', function () {
    ['tenant' => $tenant] = createPurchasableCartTenant();

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'وحدة A',
        'type' => 'unit',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $unit = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('unit-rental'),
        'title' => 'شقة فاخرة',
        'slug' => 'luxury-apartment',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 25000],
    ]);

    $unit->calendars()->attach($calendar->id, ['type' => 'unit', 'active' => true]);

    $component = Livewire::test(UnitRentalDetail::class, ['slug' => $unit->slug]);

    $component
        ->set('checkIn', '2026-07-06')
        ->set('checkOut', '2026-07-08')
        ->call('addToCart');

    $component
        ->set('checkIn', '2026-07-06')
        ->set('checkOut', '2026-07-08')
        ->call('addToCart');

    expect(CartItem::query()->count())->toBe(1);
});

it('adds different purchasable content types through the cart service', function () {
    ['tenant' => $tenant] = createPurchasableCartTenant();

    $digitalService = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('digital-services'),
        'title' => 'تصميم شعار',
        'slug' => 'logo-design',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 12000],
    ]);

    $course = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('courses'),
        'title' => 'دورة Laravel',
        'slug' => 'laravel-course',
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 8000],
    ]);

    $cart = app(CartService::class);
    $cart->addItem($digitalService);
    $cart->addItem($course);

    $types = CartItem::query()->orderBy('id')->get()->map->itemType()->all();

    expect($types)->toBe(['digital_service', 'course']);
});

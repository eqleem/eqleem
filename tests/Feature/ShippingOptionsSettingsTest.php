<?php

use App\Models\City;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Support\ShippingMethodRegistry;
use App\Support\WorldLocationOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTenantWithUserForShippingOptions(): array
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

it('registers shipping options in settings config', function () {
    expect(config('settings.shipping-option.slug'))->toBe('shipping-option')
        ->and(config('settings.shipping-option.name'))->toBe('وسائل الشحن')
        ->and(config('settings.shipping-option.order'))->toBe(11)
        ->and(config('settings.shipping-option.components.index'))->toBe('admin::settings.shipping-options.shipping-options');
});

it('defines eqleem ship in shipping methods config', function () {
    $methods = app(ShippingMethodRegistry::class)->all();

    expect($methods)->toHaveCount(1)
        ->and($methods->first()->slug)->toBe('eqleem-ship')
        ->and($methods->first()->name)->toBe('إقليم شيب - شحن عادي');
});

it('toggles a shipping method active state for the tenant', function () {
    [$user] = createTenantWithUserForShippingOptions();

    Livewire::actingAs($user)
        ->test('admin::settings.shipping-options.shipping-options')
        ->call('toggleMethodActive', 'eqleem-ship')
        ->assertHasNoErrors();

    expect(Setting::shippingMethod('eqleem-ship')['active'])->toBeTrue();

    Livewire::actingAs($user)
        ->test('admin::settings.shipping-options.shipping-options')
        ->call('toggleMethodActive', 'eqleem-ship')
        ->assertHasNoErrors();

    expect(Setting::shippingMethod('eqleem-ship')['active'])->toBeFalse();
});

it('saves eqleem ship settings from the modal', function () {
    [$user] = createTenantWithUserForShippingOptions();

    Livewire::actingAs($user)
        ->test('admin::settings.shipping-options.shipping-options')
        ->call('toggleMethodActive', 'eqleem-ship');

    Livewire::actingAs($user)
        ->test('admin::settings.shipping-options.modals.eqleem-ship', ['slug' => 'eqleem-ship'])
        ->set('label', 'شحن سريع')
        ->set('domesticPrice', '25')
        ->set('gulfPrice', '45')
        ->set('internationalPrice', '85')
        ->call('submit')
        ->assertHasNoErrors();

    $saved = Setting::shippingMethod('eqleem-ship');

    expect($saved['active'])->toBeTrue()
        ->and($saved['label'])->toBe('شحن سريع')
        ->and($saved['domestic_price'])->toEqual(25)
        ->and($saved['gulf_price'])->toEqual(45)
        ->and($saved['international_price'])->toEqual(85);
});

it('creates a custom shipping option with selected cities', function () {
    [$user] = createTenantWithUserForShippingOptions();

    $cityId = (string) City::query()->active()->whereHas('country', fn ($query) => $query->where('iso2', 'SA'))->value('id');

    Livewire::actingAs($user)
        ->test('admin::settings.shipping-options.custom-shipping-form')
        ->set('name', 'محمد للمندوب')
        ->set('price', '24')
        ->set('country', 'SA')
        ->set('cityIds', [$cityId])
        ->call('submit')
        ->assertHasNoErrors();

    $items = Setting::customShippingOptions();

    expect($items)->toHaveCount(1)
        ->and($items[0]['name'])->toBe('محمد للمندوب')
        ->and($items[0]['price'])->toEqual(24)
        ->and($items[0]['country'])->toBe('SA')
        ->and($items[0]['city_ids'])->toBe([$cityId])
        ->and($items[0]['active'])->toBeTrue();
});

it('creates a custom shipping option for all countries and cities', function () {
    [$user] = createTenantWithUserForShippingOptions();

    Livewire::actingAs($user)
        ->test('admin::settings.shipping-options.custom-shipping-form')
        ->set('name', 'شحن عالمي')
        ->set('price', '50')
        ->set('country', WorldLocationOptions::ALL_COUNTRIES)
        ->call('submit')
        ->assertHasNoErrors();

    $items = Setting::customShippingOptions();

    expect($items)->toHaveCount(1)
        ->and($items[0]['country'])->toBe(WorldLocationOptions::ALL_COUNTRIES)
        ->and($items[0]['all_cities'])->toBeTrue()
        ->and($items[0]['city_ids'])->toBe([]);
});

it('deletes a custom shipping option', function () {
    [$user] = createTenantWithUserForShippingOptions();

    Setting::saveCustomShippingOptions([[
        'id' => 'custom-1',
        'name' => 'محمد للمندوب',
        'price' => 24,
        'country' => 'SA',
        'all_cities' => true,
        'city_ids' => [WorldLocationOptions::ALL_CITIES],
        'active' => true,
    ]]);

    Livewire::actingAs($user)
        ->test('admin::settings.shipping-options.custom-shipping-form', ['optionId' => 'custom-1'])
        ->call('deleteOption')
        ->assertHasNoErrors();

    expect(Setting::customShippingOptions())->toBe([]);
});

it('renders the shipping options settings page', function () {
    [$user] = createTenantWithUserForShippingOptions();

    Livewire::actingAs($user)
        ->test('admin::settings.shipping-options.shipping-options')
        ->assertSee('وسائل الشحن')
        ->assertSee('إقليم شيب - شحن عادي')
        ->assertSee('خيارات الشحن المخصصة')
        ->assertSee('أضف خدمة شحن');
});

it('orders countries with priority and all countries option', function () {
    $options = app(WorldLocationOptions::class)->countrySelectOptions();

    expect($options[0]['id'])->toBe(WorldLocationOptions::ALL_COUNTRIES)
        ->and($options[1]['label'])->toBe('الدول المفضلة')
        ->and(collect($options)->firstWhere('id', 'SA'))->not->toBeNull();
});

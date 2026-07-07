<?php

use App\Models\City;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CheckoutShippingService;
use App\Support\WorldLocationOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createCheckoutShippingTenant(): Tenant
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Shipping Tenant',
        'handle' => 'shipping-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    return $tenant;
}

it('calculates eqleem ship domestic gulf and international fees', function () {
    createCheckoutShippingTenant();

    Setting::saveShippingMethod('eqleem-ship', [
        'domestic_price' => 25,
        'gulf_price' => 45,
        'international_price' => 85,
    ], true);

    $service = app(CheckoutShippingService::class);

    expect($service->eqleemShipFee('SA'))->toBe(2500)
        ->and($service->eqleemShipFee('AE'))->toBe(4500)
        ->and($service->eqleemShipFee('US'))->toBe(8500);
});

it('returns active registry and custom shipping options for checkout', function () {
    createCheckoutShippingTenant();

    Setting::saveShippingMethod('eqleem-ship', [
        'label' => 'شحن سريع',
        'domestic_price' => 20,
    ], true);

    $cityId = (string) City::query()
        ->active()
        ->whereHas('country', fn ($query) => $query->where('iso2', 'SA'))
        ->value('id');

    Setting::saveCustomShippingOptions([[
        'id' => 'custom-riyadh',
        'name' => 'مندوب الرياض',
        'price' => 18,
        'country' => 'SA',
        'all_cities' => false,
        'city_ids' => [$cityId],
        'active' => true,
    ]]);

    $service = app(CheckoutShippingService::class);
    $options = $service->availableOptions('SA', $cityId);

    expect($options)->toHaveCount(2)
        ->and($options->pluck('name')->all())->toContain('شحن سريع', 'مندوب الرياض')
        ->and($service->fee($service->customMethodKey('custom-riyadh'), 'SA', $cityId))->toBe(1800);
});

it('filters custom shipping options by country and city', function () {
    createCheckoutShippingTenant();

    $cityId = (string) City::query()
        ->active()
        ->whereHas('country', fn ($query) => $query->where('iso2', 'SA'))
        ->value('id');

    Setting::saveCustomShippingOptions([[
        'id' => 'custom-sa',
        'name' => 'شحن السعودية',
        'price' => 15,
        'country' => 'SA',
        'all_cities' => true,
        'city_ids' => [WorldLocationOptions::ALL_CITIES],
        'active' => true,
    ], [
        'id' => 'custom-global',
        'name' => 'شحن عالمي',
        'price' => 60,
        'country' => WorldLocationOptions::ALL_COUNTRIES,
        'all_cities' => true,
        'city_ids' => [],
        'active' => true,
    ]]);

    $service = app(CheckoutShippingService::class);

    expect($service->availableOptions('SA', $cityId))->toHaveCount(2)
        ->and($service->availableOptions('AE', null))->toHaveCount(1)
        ->and($service->availableOptions('AE', null)->first()['name'])->toBe('شحن عالمي');
});

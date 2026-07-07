<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

use App\Models\City;
use App\Models\Setting;
use App\Services\CheckoutShippingService;
use Database\Seeders\WorldSeeder;
use Livewire\Features\SupportTesting\Testable;

function enableStoreCheckoutShipping(float $domesticPrice = 25): string
{
    Setting::saveShippingMethod('eqleem-ship', [
        'label' => 'شحن إقليم',
        'domestic_price' => $domesticPrice,
        'gulf_price' => 45,
        'international_price' => 85,
    ], true);

    return app(CheckoutShippingService::class)->registryMethodKey('eqleem-ship');
}

/**
 * @return array{address: string, country: string, cityId: string, neighborhood: string}
 */
function checkoutShippingAddress(): array
{
    static $cached = null;

    if (is_array($cached)) {
        return $cached;
    }

    $cityId = City::query()
        ->active()
        ->whereHas('country', fn ($query) => $query->where('iso2', 'SA'))
        ->value('id');

    if (! $cityId) {
        (new WorldSeeder)->run();

        $cityId = City::query()
            ->active()
            ->whereHas('country', fn ($query) => $query->where('iso2', 'SA'))
            ->value('id');
    }

    return $cached = [
        'address' => 'شارع الملك فهد',
        'country' => 'SA',
        'cityId' => (string) $cityId,
        'neighborhood' => 'حي العليا',
    ];
}

function fillCheckoutShipping(Testable $test): Testable
{
    $address = checkoutShippingAddress();

    return $test
        ->set('address', $address['address'])
        ->set('country', $address['country'])
        ->set('cityId', $address['cityId'])
        ->set('neighborhood', $address['neighborhood']);
}

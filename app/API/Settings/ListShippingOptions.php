<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\ShippingOptionsResource;
use App\Models\Setting;
use App\Models\Tenant;
use App\Support\ShippingMethodRegistry;
use App\Support\WorldLocationOptions;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists shipping methods and custom shipping options.
 */
class ListShippingOptions
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant): array
    {
        setCurrentTenant($tenant);

        $methods = app(ShippingMethodRegistry::class)->all()
            ->map(function ($method): array {
                $saved = Setting::shippingMethod($method->slug);

                return [
                    'slug' => $method->slug,
                    'name' => $method->name,
                    'description' => $method->description,
                    'icon' => $method->icon,
                    'icon_url' => asset($method->icon),
                    'active' => (bool) data_get($saved, 'active', false),
                    'settings' => collect($saved)->except('active')->all(),
                    'order' => $method->order,
                ];
            })
            ->values()
            ->all();

        $locations = app(WorldLocationOptions::class);
        $countryMap = $locations->countryMap();

        $customOptions = collect(Setting::customShippingOptions())
            ->map(function (array $option) use ($countryMap): array {
                return [
                    'id' => (string) ($option['id'] ?? ''),
                    'name' => (string) ($option['name'] ?? ''),
                    'price' => (float) ($option['price'] ?? 0),
                    'country' => (string) ($option['country'] ?? '*'),
                    'country_label' => (string) ($countryMap[$option['country'] ?? '*'] ?? ($option['country'] ?? '*')),
                    'all_cities' => (bool) ($option['all_cities'] ?? false),
                    'city_ids' => array_values((array) ($option['city_ids'] ?? [])),
                    'active' => (bool) ($option['active'] ?? true),
                ];
            })
            ->values()
            ->all();

        return [
            'methods' => $methods,
            'custom_options' => $customOptions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request): array
    {
        return $this->handle($this->currentDashboardTenant($request));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): ShippingOptionsResource
    {
        return new ShippingOptionsResource($payload);
    }
}

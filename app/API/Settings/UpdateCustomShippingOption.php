<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\CustomShippingOptionResource;
use App\Models\Setting;
use App\Models\Tenant;
use App\Support\WorldLocationOptions;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Updates a custom shipping option.
 */
class UpdateCustomShippingOption
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $locations = app(WorldLocationOptions::class);
        $country = (string) request()->input('country', '');

        $rules = [
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'country' => ['required', 'string', Rule::in($locations->selectableCountryIds())],
            'all_cities' => ['sometimes', 'boolean'],
            'city_ids' => ['sometimes', 'array'],
            'city_ids.*' => ['string'],
            'active' => ['sometimes', 'boolean'],
        ];

        if (
            $country !== ''
            && $country !== WorldLocationOptions::ALL_COUNTRIES
            && ! request()->boolean('all_cities')
        ) {
            $rules['city_ids'] = ['required', 'array', 'min:1'];
            $rules['city_ids.*'] = [
                'required',
                'string',
                Rule::in($locations->selectableCityIds($country, (array) request()->input('city_ids', []))),
            ];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, string $id, array $data): array
    {
        setCurrentTenant($tenant);

        $items = Setting::customShippingOptions();
        $found = false;
        $payload = null;

        foreach ($items as $index => $item) {
            if (($item['id'] ?? null) !== $id) {
                continue;
            }

            $payload = $this->normalizeOption($data, $id);
            $items[$index] = $payload;
            $found = true;
            break;
        }

        if (! $found || $payload === null) {
            throw new NotFoundHttpException;
        }

        Setting::saveCustomShippingOptions($items);

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, string $id): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $id, $validated);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): CustomShippingOptionResource
    {
        return (new CustomShippingOptionResource($payload))
            ->additional([
                'message' => __('Settings updated successfully.'),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{id: string, name: string, price: float, country: string, all_cities: bool, city_ids: list<string>, active: bool}
     */
    private function normalizeOption(array $data, string $id): array
    {
        $country = (string) $data['country'];
        $allCities = (bool) ($data['all_cities'] ?? false);
        $cityIds = array_values(array_map('strval', (array) ($data['city_ids'] ?? [])));

        if (in_array(WorldLocationOptions::ALL_CITIES, $cityIds, true)) {
            $allCities = true;
            $cityIds = [WorldLocationOptions::ALL_CITIES];
        }

        if ($country === WorldLocationOptions::ALL_COUNTRIES) {
            $allCities = true;
            $cityIds = [];
        } elseif ($allCities) {
            $cityIds = [WorldLocationOptions::ALL_CITIES];
        }

        return [
            'id' => $id,
            'name' => trim((string) $data['name']),
            'price' => (float) $data['price'],
            'country' => $country,
            'all_cities' => $allCities,
            'city_ids' => $cityIds,
            'active' => (bool) ($data['active'] ?? true),
        ];
    }
}

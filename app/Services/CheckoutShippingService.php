<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\ShippingMethodRegistry;
use App\Support\WorldLocationOptions;
use Illuminate\Support\Collection;

class CheckoutShippingService
{
    public const REGISTRY_PREFIX = 'registry:';

    public const CUSTOM_PREFIX = 'custom:';

    /** @var list<string> */
    public const GULF_COUNTRIES = ['AE', 'BH', 'KW', 'OM', 'QA'];

    /** @var array<string, float> */
    public const EQLEEM_SHIP_DEFAULT_PRICES = [
        'domestic' => 21,
        'gulf' => 41,
        'international' => 81,
    ];

    public function __construct(
        protected ShippingMethodRegistry $registry,
        protected WorldLocationOptions $locations,
    ) {}

    public function registryMethodKey(string $slug): string
    {
        return self::REGISTRY_PREFIX.$slug;
    }

    public function customMethodKey(string $id): string
    {
        return self::CUSTOM_PREFIX.$id;
    }

    /**
     * @return array{type: 'registry'|'custom', id: string}|null
     */
    public function parseMethodKey(string $methodKey): ?array
    {
        if (str_starts_with($methodKey, self::REGISTRY_PREFIX)) {
            $id = substr($methodKey, strlen(self::REGISTRY_PREFIX));

            return filled($id) ? ['type' => 'registry', 'id' => $id] : null;
        }

        if (str_starts_with($methodKey, self::CUSTOM_PREFIX)) {
            $id = substr($methodKey, strlen(self::CUSTOM_PREFIX));

            return filled($id) ? ['type' => 'custom', 'id' => $id] : null;
        }

        return null;
    }

    /**
     * @return Collection<int, array{key: string, name: string, description: string, icon_url: ?string, fee: int, requires_country: bool}>
     */
    public function availableOptions(?string $country, ?string $cityId): Collection
    {
        $options = collect();

        foreach ($this->registry->activeForCheckout() as $method) {
            $slug = (string) ($method['slug'] ?? '');

            if ($slug === '') {
                continue;
            }

            $name = filled($method['label'] ?? null)
                ? (string) $method['label']
                : (string) ($method['name'] ?? $slug);

            $options->push([
                'key' => $this->registryMethodKey($slug),
                'name' => $name,
                'description' => (string) ($method['description'] ?? ''),
                'icon_url' => $method['icon_url'] ?? null,
                'fee' => $this->registryFee($slug, $country),
                'requires_country' => $slug === 'eqleem-ship',
            ]);
        }

        foreach ($this->activeCustomOptions($country, $cityId) as $option) {
            $options->push([
                'key' => $this->customMethodKey((string) $option['id']),
                'name' => (string) $option['name'],
                'description' => $this->customOptionDescription($option),
                'icon_url' => null,
                'fee' => money_minor((float) ($option['price'] ?? 0)),
                'requires_country' => false,
            ]);
        }

        return $options->values();
    }

    public function fee(string $methodKey, ?string $country, ?string $cityId): int
    {
        $parsed = $this->parseMethodKey($methodKey);

        if (! is_array($parsed)) {
            return 0;
        }

        if ($parsed['type'] === 'registry') {
            return $this->registryFee($parsed['id'], $country);
        }

        $option = $this->findCustomOption($parsed['id']);

        if (! is_array($option) || ! $this->matchesCustomOption($option, $country, $cityId)) {
            return 0;
        }

        return money_minor((float) ($option['price'] ?? 0));
    }

    public function label(string $methodKey): string
    {
        $parsed = $this->parseMethodKey($methodKey);

        if (! is_array($parsed)) {
            return $methodKey;
        }

        if ($parsed['type'] === 'registry') {
            $method = $this->registry->find($parsed['id']);
            $settings = Setting::shippingMethod($parsed['id']);

            if (filled($settings['label'] ?? null)) {
                return (string) $settings['label'];
            }

            return $method?->name ?? $parsed['id'];
        }

        $option = $this->findCustomOption($parsed['id']);

        return is_array($option) ? (string) ($option['name'] ?? $methodKey) : $methodKey;
    }

    public function isValidMethod(string $methodKey, ?string $country, ?string $cityId): bool
    {
        return $this->availableOptions($country, $cityId)
            ->contains(fn (array $option): bool => $option['key'] === $methodKey);
    }

    /**
     * @return list<string>
     */
    public function availableMethodKeys(?string $country, ?string $cityId): array
    {
        return $this->availableOptions($country, $cityId)
            ->pluck('key')
            ->all();
    }

    public function eqleemShipZone(?string $country): string
    {
        $country = strtoupper((string) $country);

        if ($country === 'SA') {
            return 'domestic';
        }

        if (in_array($country, self::GULF_COUNTRIES, true)) {
            return 'gulf';
        }

        return 'international';
    }

    public function eqleemShipFee(?string $country): int
    {
        if (blank($country)) {
            return 0;
        }

        $settings = Setting::shippingMethod('eqleem-ship');
        $zone = $this->eqleemShipZone($country);
        $priceKey = match ($zone) {
            'domestic' => 'domestic_price',
            'gulf' => 'gulf_price',
            default => 'international_price',
        };
        $defaultKey = match ($zone) {
            'domestic' => 'domestic',
            'gulf' => 'gulf',
            default => 'international',
        };

        $configured = data_get($settings, $priceKey);

        if ($configured !== null && $configured !== '') {
            return money_minor((float) $configured);
        }

        return money_minor(self::EQLEEM_SHIP_DEFAULT_PRICES[$defaultKey]);
    }

    /**
     * @return array{address: string, country: string, city_id: string, neighborhood: string, country_label: string, city_label: string}
     */
    public function normalizeAddress(array $address): array
    {
        $country = strtoupper((string) ($address['country'] ?? ''));
        $cityId = (string) ($address['city_id'] ?? $address['cityId'] ?? '');

        return [
            'address' => trim((string) ($address['address'] ?? '')),
            'country' => $country,
            'city_id' => $cityId,
            'neighborhood' => trim((string) ($address['neighborhood'] ?? '')),
            'country_label' => $this->locations->countryLabel($country),
            'city_label' => $cityId !== ''
                ? ($this->locations->cityLabelsByIds([(int) $cityId])[(int) $cityId] ?? '')
                : '',
        ];
    }

    protected function registryFee(string $slug, ?string $country): int
    {
        return match ($slug) {
            'eqleem-ship' => $this->eqleemShipFee($country),
            default => 0,
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function activeCustomOptions(?string $country, ?string $cityId): array
    {
        return collect(Setting::customShippingOptions())
            ->filter(fn (array $option): bool => (bool) ($option['active'] ?? true))
            ->filter(fn (array $option): bool => $this->matchesCustomOption($option, $country, $cityId))
            ->sortBy('name')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $option
     */
    public function matchesCustomOption(array $option, ?string $country, ?string $cityId): bool
    {
        $optionCountry = (string) ($option['country'] ?? WorldLocationOptions::ALL_COUNTRIES);
        $normalizedCountry = strtoupper((string) $country);

        if ($optionCountry !== WorldLocationOptions::ALL_COUNTRIES) {
            if ($normalizedCountry === '' || $optionCountry !== $normalizedCountry) {
                return false;
            }
        }

        if (($option['all_cities'] ?? false) || $optionCountry === WorldLocationOptions::ALL_COUNTRIES) {
            return true;
        }

        if (blank($cityId)) {
            return false;
        }

        $cityIds = collect($option['city_ids'] ?? [])
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        if ($cityIds === [WorldLocationOptions::ALL_CITIES] || in_array(WorldLocationOptions::ALL_CITIES, $cityIds, true)) {
            return true;
        }

        return in_array((string) $cityId, $cityIds, true);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function findCustomOption(string $id): ?array
    {
        return collect(Setting::customShippingOptions())
            ->first(fn (array $option): bool => (string) ($option['id'] ?? '') === $id);
    }

    /**
     * @param  array<string, mixed>  $option
     */
    protected function customOptionDescription(array $option): string
    {
        $country = (string) ($option['country'] ?? WorldLocationOptions::ALL_COUNTRIES);
        $cityLabels = $this->locations->cityLabels(
            $country,
            (array) ($option['city_ids'] ?? []),
            (bool) ($option['all_cities'] ?? false),
        );

        $parts = [$this->locations->countryLabel($country)];

        if ($cityLabels !== []) {
            $parts[] = implode('، ', array_slice($cityLabels, 0, 2));
        }

        return implode(' · ', array_filter($parts));
    }
}

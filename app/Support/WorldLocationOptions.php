<?php

namespace App\Support;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Support\Collection;

class WorldLocationOptions
{
    public const ALL_COUNTRIES = '*';

    public const ALL_CITIES = '*';

    /** @var list<string> */
    private const PRIORITY_COUNTRIES = ['SA', 'AE', 'BH', 'KW', 'OM', 'QA'];

    /** @var list<string> */
    private const ARAB_COUNTRIES = [
        'DZ', 'BH', 'KM', 'DJ', 'EG', 'IQ', 'JO', 'KW', 'LB', 'LY',
        'MR', 'MA', 'OM', 'PS', 'QA', 'SA', 'SO', 'SD', 'SY', 'TN',
        'AE', 'YE',
    ];

    /**
     * @return list<array{id: string, label: string, selectable?: bool}>
     */
    public function countrySelectOptions(bool $includeAll = true): array
    {
        $countries = Country::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'iso2', 'emoji', 'translations']);

        $grouped = $this->groupCountries($countries);
        $options = [];

        if ($includeAll) {
            $options[] = [
                'id' => self::ALL_COUNTRIES,
                'label' => 'كل الدول',
            ];
        }

        foreach ($grouped as $groupLabel => $items) {
            $options[] = [
                'id' => 'group-'.$groupLabel,
                'label' => $groupLabel,
                'selectable' => false,
            ];

            foreach ($items as $country) {
                $options[] = [
                    'id' => $country['iso2'],
                    'label' => $country['label'],
                ];
            }
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public function countryMap(): array
    {
        $options = [];

        foreach ($this->countrySelectOptions(includeAll: true) as $option) {
            if (! ($option['selectable'] ?? true)) {
                continue;
            }

            $options[$option['id']] = $option['label'];
        }

        return $options;
    }

    /**
     * @return list<array{id: string, label: string, selectable?: bool}>
     */
    public function citySelectOptions(string $countryIso2, ?string $search = null): array
    {
        if ($countryIso2 === self::ALL_COUNTRIES) {
            return [];
        }

        $country = Country::query()
            ->active()
            ->where('iso2', strtoupper($countryIso2))
            ->first(['id', 'iso2']);

        if (! $country) {
            return [];
        }

        $query = City::query()
            ->active()
            ->where('country_id', $country->id)
            ->with(['state:id,name,translations'])
            ->orderBy('name');

        if (filled($search)) {
            $term = '%'.mb_strtolower(trim($search)).'%';
            $query->where(function ($builder) use ($term): void {
                $builder
                    ->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(json_extract(translations, "$.ar")) LIKE ?', [$term]);
            });
        }

        $cities = $query->limit(200)->get(['id', 'name', 'state_id', 'translations']);

        $options = [
            [
                'id' => self::ALL_CITIES,
                'label' => 'كل المدن داخل الدولة',
            ],
        ];

        $grouped = $cities
            ->groupBy(fn (City $city): string => $this->localizedName($city->state, 'مناطق أخرى'))
            ->sortKeys();

        foreach ($grouped as $region => $regionCities) {
            $options[] = [
                'id' => 'group-'.$region,
                'label' => $region,
                'selectable' => false,
            ];

            foreach ($regionCities->sortBy(fn (City $city): string => $this->localizedName($city)) as $city) {
                $options[] = [
                    'id' => (string) $city->id,
                    'label' => $this->localizedName($city),
                ];
            }
        }

        return $options;
    }

    public function countryLabel(string $iso2): string
    {
        if ($iso2 === self::ALL_COUNTRIES) {
            return 'كل الدول';
        }

        return $this->countryMap()[$iso2] ?? $iso2;
    }

    /**
     * @param  list<string|int>  $cityIds
     * @return list<string>
     */
    public function cityLabels(string $countryIso2, array $cityIds, bool $allCities = false): array
    {
        if ($countryIso2 === self::ALL_COUNTRIES || $allCities) {
            return ['كل المدن'];
        }

        if ($cityIds === [self::ALL_CITIES] || in_array(self::ALL_CITIES, $cityIds, true)) {
            return ['كل المدن داخل الدولة'];
        }

        $ids = collect($cityIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->values()
            ->all();

        if ($ids === []) {
            return [];
        }

        return City::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'translations'])
            ->map(fn (City $city): string => $this->localizedName($city))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Country>  $countries
     * @return array<string, list<array{iso2: string, label: string}>>
     */
    protected function groupCountries(Collection $countries): array
    {
        $priority = [];
        $arab = [];
        $rest = [];

        foreach ($countries as $country) {
            $item = [
                'iso2' => $country->iso2,
                'label' => trim(($country->emoji ? $country->emoji.' ' : '').$this->localizedName($country)),
            ];

            if (in_array($country->iso2, self::PRIORITY_COUNTRIES, true)) {
                $priority[$country->iso2] = $item;

                continue;
            }

            if (in_array($country->iso2, self::ARAB_COUNTRIES, true)) {
                $arab[] = $item;

                continue;
            }

            $rest[] = $item;
        }

        $orderedPriority = collect(self::PRIORITY_COUNTRIES)
            ->map(fn (string $iso2): ?array => $priority[$iso2] ?? null)
            ->filter()
            ->values()
            ->all();

        usort($arab, fn (array $a, array $b): int => strcmp($a['label'], $b['label']));
        usort($rest, fn (array $a, array $b): int => strcmp($a['label'], $b['label']));

        return array_filter([
            'الدول المفضلة' => $orderedPriority,
            'الدول العربية' => $arab,
            'دول أخرى' => $rest,
        ], fn (array $items): bool => $items !== []);
    }

    protected function localizedName(Country|State|City|null $model, string $fallback = ''): string
    {
        if (! $model) {
            return $fallback;
        }

        $locale = app()->getLocale();

        return (string) data_get($model->translations, $locale, $model->name ?? $fallback);
    }
}

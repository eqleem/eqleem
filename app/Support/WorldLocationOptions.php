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

    private const MIN_CITY_SEARCH_LENGTH = 2;

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
        $grouped = once(fn (): array => $this->groupCountries($this->activeCountries()));
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
        return [self::ALL_COUNTRIES => 'كل الدول'] + $this->countryLabelsMap();
    }

    /**
     * @param  list<string|int>  $ensureIds
     * @return list<array{id: string, label: string, selectable?: bool}>
     */
    public function citySelectOptions(string $countryIso2, ?string $search = null, array $ensureIds = []): array
    {
        if ($countryIso2 === self::ALL_COUNTRIES) {
            return [];
        }

        $options = [
            [
                'id' => self::ALL_CITIES,
                'label' => 'كل المدن داخل الدولة',
            ],
        ];

        $search = filled($search) ? trim($search) : null;

        if ($search !== null && mb_strlen($search) >= self::MIN_CITY_SEARCH_LENGTH) {
            $country = $this->findActiveCountryByIso2($countryIso2);

            if ($country) {
                $options = array_merge($options, $this->buildCityOptionsForCountry($country, $search));
            }
        }

        if ($ensureIds !== []) {
            $options = $this->ensureCityOptions($options, $countryIso2, $ensureIds);
        }

        return $options;
    }

    /**
     * @param  list<string|int>  $ensureIds
     * @return list<string>
     */
    public function selectableCityIds(string $countryIso2, array $ensureIds = []): array
    {
        $ids = [self::ALL_CITIES];

        if ($ensureIds === []) {
            return $ids;
        }

        $country = $this->findActiveCountryByIso2($countryIso2);

        if (! $country) {
            return $ids;
        }

        $cityIds = City::query()
            ->active()
            ->where('country_id', $country->id)
            ->whereIn('id', $this->normalizeCityIds($ensureIds))
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        return array_values(array_unique([...$ids, ...$cityIds]));
    }

    /**
     * @return list<string>
     */
    public function selectableCountryIds(): array
    {
        return array_keys($this->countryMap());
    }

    public function countryLabel(string $iso2): string
    {
        if ($iso2 === self::ALL_COUNTRIES) {
            return 'كل الدول';
        }

        return $this->countryLabelsMap()[$iso2] ?? $iso2;
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

        $ids = $this->normalizeCityIds($cityIds);

        if ($ids === []) {
            return [];
        }

        return collect($this->cityLabelsByIds($ids))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  list<int|string>  $ids
     * @return array<int, string>
     */
    public function cityLabelsByIds(array $ids): array
    {
        $ids = $this->normalizeCityIds($ids);

        if ($ids === []) {
            return [];
        }

        return City::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name_en', 'name_ar'])
            ->mapWithKeys(fn (City $city): array => [
                $city->id => $this->localizedName($city, locale: 'ar'),
            ])
            ->all();
    }

    /**
     * @return Collection<int, Country>
     */
    protected function activeCountries(): Collection
    {
        return once(function (): Collection {
            return Country::query()
                ->active()
                ->orderBy(app()->getLocale() === 'ar' ? 'name_ar' : 'name_en')
                ->get(['id', 'name_en', 'name_ar', 'iso2']);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function countryLabelsMap(): array
    {
        return once(function (): array {
            return $this->activeCountries()
                ->mapWithKeys(fn (Country $country): array => [
                    $country->iso2 => $this->localizedName($country),
                ])
                ->all();
        });
    }

    protected function findActiveCountryByIso2(string $countryIso2): ?Country
    {
        return $this->activeCountries()
            ->firstWhere('iso2', strtoupper($countryIso2));
    }

    /**
     * @return list<array{id: string, label: string, selectable?: bool}>
     */
    protected function buildCityOptionsForCountry(Country $country, string $search): array
    {
        $term = '%'.mb_strtolower(trim($search)).'%';

        $cities = City::query()
            ->active()
            ->where('country_id', $country->id)
            ->with(['state:id,name_en,name_ar'])
            ->where(function ($builder) use ($term, $country, $search): void {
                $builder->whereRaw('LOWER(name_en) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(name_ar) LIKE ?', [$term]);

                if ($country->iso2 === 'SA') {
                    $englishNames = $this->matchingSaCityEnglishNames($search);

                    if ($englishNames !== []) {
                        $builder->orWhereIn('name_en', $englishNames);
                    }
                }
            })
            ->orderBy(app()->getLocale() === 'ar' ? 'name_ar' : 'name_en')
            ->limit(200)
            ->get(['id', 'name_en', 'name_ar', 'state_id', 'country_id']);

        return $this->formatCityOptions($cities);
    }

    /**
     * @param  Collection<int, City>  $cities
     * @return list<array{id: string, label: string, selectable?: bool}>
     */
    protected function formatCityOptions(Collection $cities): array
    {
        $options = [];

        $grouped = $cities
            ->groupBy(fn (City $city): string => $this->localizedName($city->state, 'مناطق أخرى', locale: 'ar'))
            ->sortKeys(SORT_STRING);

        foreach ($grouped as $region => $regionCities) {
            $options[] = [
                'id' => 'group-'.$region,
                'label' => $region,
                'selectable' => false,
            ];

            foreach ($regionCities->sortBy(fn (City $city): string => $this->localizedName($city, locale: 'ar'), SORT_STRING) as $city) {
                $options[] = [
                    'id' => (string) $city->id,
                    'label' => $this->localizedName($city, locale: 'ar'),
                ];
            }
        }

        return $options;
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
                'label' => $this->localizedName($country),
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

    /**
     * @param  list<array{id: string, label: string, selectable?: bool}>  $options
     * @param  list<string|int>  $ensureIds
     * @return list<array{id: string, label: string, selectable?: bool}>
     */
    protected function ensureCityOptions(array $options, string $countryIso2, array $ensureIds): array
    {
        $presentIds = collect($options)
            ->filter(fn (array $option): bool => $option['selectable'] ?? true)
            ->pluck('id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        $missingIds = collect($ensureIds)
            ->map(fn (mixed $id): string => (string) $id)
            ->reject(fn (string $id): bool => $id === self::ALL_CITIES || in_array($id, $presentIds, true))
            ->map(fn (string $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->values()
            ->all();

        if ($missingIds === []) {
            return $options;
        }

        $country = $this->findActiveCountryByIso2($countryIso2);

        if (! $country) {
            return $options;
        }

        $missingCities = City::query()
            ->active()
            ->where('country_id', $country->id)
            ->whereIn('id', $missingIds)
            ->with(['state:id,name_en,name_ar'])
            ->get(['id', 'name_en', 'name_ar', 'state_id', 'country_id']);

        $insertOptions = $missingCities
            ->sortBy(fn (City $city): string => $this->localizedName($city, locale: 'ar'), SORT_STRING)
            ->map(fn (City $city): array => [
                'id' => (string) $city->id,
                'label' => $this->localizedName($city, locale: 'ar'),
            ])
            ->values()
            ->all();

        if ($insertOptions === []) {
            return $options;
        }

        array_splice($options, 1, 0, $insertOptions);

        return $options;
    }

    /**
     * @param  list<string|int>  $cityIds
     * @return list<int>
     */
    protected function normalizeCityIds(array $cityIds): array
    {
        return collect($cityIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function localizedName(Country|State|City|null $model, string $fallback = '', string $locale = 'ar'): string
    {
        if (! $model) {
            return $fallback;
        }

        $name = $locale === 'ar'
            ? ($model->name_ar ?: $model->name_en)
            : ($model->name_en ?: $model->name_ar);

        if (is_string($name) && $name !== '') {
            return $name;
        }

        return $fallback;
    }

    /**
     * @return list<string>
     */
    protected function matchingSaCityEnglishNames(string $search): array
    {
        $needle = mb_strtolower(trim($search));

        if ($needle === '') {
            return [];
        }

        return collect($this->saCitiesLiteDataset())
            ->filter(function (mixed $city) use ($needle): bool {
                if (! is_array($city)) {
                    return false;
                }

                $arabicName = mb_strtolower((string) ($city['name_ar'] ?? ''));
                $englishName = mb_strtolower((string) ($city['name_en'] ?? ''));

                return str_contains($arabicName, $needle) || str_contains($englishName, $needle);
            })
            ->pluck('name_en')
            ->map(fn (mixed $name): string => (string) $name)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function saCitiesLiteDataset(): array
    {
        return once(function (): array {
            $path = database_path('data-light/cities_lite.json');

            if (! is_file($path)) {
                return [];
            }

            $cities = json_decode((string) file_get_contents($path), true);

            return is_array($cities) ? $cities : [];
        });
    }
}

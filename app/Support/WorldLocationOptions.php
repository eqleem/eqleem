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

    /** @var array<string, string> */
    private const SA_STATE_ALIASES = [
        "'Asir" => 'Asir',
        'Al Bahah' => 'Bahah',
        'Al Jawf' => 'Jawf',
        'Al Madinah' => 'Madinah',
        'Al-Qassim' => 'Qassim',
        "Ha'il" => 'Hail',
        'Jizan' => 'Jazan',
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
            $query->where(function ($builder) use ($term, $country, $search): void {
                $builder
                    ->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(json_extract(translations, "$.ar")) LIKE ?', [$term]);

                if ($country->iso2 === 'SA') {
                    $arabicNames = $this->matchingSaCityEnglishNames($search);

                    if ($arabicNames !== []) {
                        $builder->orWhereIn('name', $arabicNames);
                    }
                }
            })->limit(200);
        } elseif ($country->iso2 === 'SA') {
            $query->limit(1000);
        } else {
            $query->limit(200);
        }

        $cities = $query->get(['id', 'name', 'state_id', 'translations', 'country_id']);

        $options = [
            [
                'id' => self::ALL_CITIES,
                'label' => 'كل المدن داخل الدولة',
            ],
        ];

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
            ->get(['id', 'name', 'translations', 'country_id'])
            ->map(fn (City $city): string => $this->localizedName($city, locale: 'ar'))
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

    protected function localizedName(Country|State|City|null $model, string $fallback = '', string $locale = 'ar'): string
    {
        if (! $model) {
            return $fallback;
        }

        $translation = data_get($model->translations, $locale);

        if (is_string($translation) && $translation !== '') {
            return $translation;
        }

        if ($model instanceof City) {
            $arabicCityName = $this->saCityArabicNames()[mb_strtolower(trim($model->name))] ?? null;

            if (is_string($arabicCityName) && $arabicCityName !== '') {
                return $arabicCityName;
            }
        }

        if ($model instanceof State) {
            $arabicStateName = $this->saStateArabicNames()[$model->name] ?? null;

            if (is_string($arabicStateName) && $arabicStateName !== '') {
                return $arabicStateName;
            }
        }

        return (string) ($model->name ?? $fallback);
    }

    /**
     * @return array<string, string>
     */
    protected function saCityArabicNames(): array
    {
        return once(function (): array {
            $path = database_path('data-light/cities_lite.json');

            if (! is_file($path)) {
                return [];
            }

            $cities = json_decode((string) file_get_contents($path), true);

            if (! is_array($cities)) {
                return [];
            }

            return collect($cities)
                ->filter(fn (mixed $city): bool => is_array($city) && filled($city['name_en'] ?? null) && filled($city['name_ar'] ?? null))
                ->mapWithKeys(fn (array $city): array => [
                    mb_strtolower(trim((string) $city['name_en'])) => (string) $city['name_ar'],
                ])
                ->all();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function saStateArabicNames(): array
    {
        return once(function (): array {
            $path = database_path('data-light/regions_lite.json');

            if (! is_file($path)) {
                return [];
            }

            $regions = json_decode((string) file_get_contents($path), true);

            if (! is_array($regions)) {
                return [];
            }

            $names = [];

            foreach ($regions as $region) {
                if (! is_array($region) || ! filled($region['name_en'] ?? null) || ! filled($region['name_ar'] ?? null)) {
                    continue;
                }

                $names[(string) $region['name_en']] = (string) $region['name_ar'];
            }

            foreach (self::SA_STATE_ALIASES as $worldName => $liteName) {
                if (isset($names[$liteName])) {
                    $names[$worldName] = $names[$liteName];
                }
            }

            return $names;
        });
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

        $path = database_path('data-light/cities_lite.json');

        if (! is_file($path)) {
            return [];
        }

        $cities = json_decode((string) file_get_contents($path), true);

        if (! is_array($cities)) {
            return [];
        }

        return collect($cities)
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
}

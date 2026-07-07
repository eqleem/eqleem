<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Language;
use App\Models\Nationality;
use App\Models\Neighborhood;
use App\Models\State;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class WorldSeeder extends Seeder
{
    protected $connection = 'world';

    private ?int $saudiArabiaId = null;

    public function run(): void
    {
        ini_set('memory_limit', '512M');

        if (DB::connection()->getName() !== 'world') {
            return;
        }

        if (Country::count() > 0) {
            return;
        }

        $this->createLanguages();
        $this->createNationalities();
        $this->createCountries();
        $this->createStates();
        $this->createCities();
        $this->createSaVillages();
        $this->createNeighborhoods();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getJsonFileAsArray(string $fileName): array
    {
        $path = database_path("data-light/{$fileName}.json");

        if (! File::exists($path)) {
            return [];
        }

        $data = json_decode(File::get($path), true);

        return is_array($data) ? $data : [];
    }

    private function saudiArabiaId(): int
    {
        if ($this->saudiArabiaId === null) {
            $this->saudiArabiaId = (int) Country::query()->where('iso2', 'SA')->value('id');
        }

        return $this->saudiArabiaId;
    }

    protected function createCountries(): void
    {
        $countries = $this->getJsonFileAsArray('countries');
        $chunkLength = 100;
        $now = now();

        $this->command?->info('Starting Seed Country Data ...');
        $this->command?->getOutput()->progressStart(count($countries));

        foreach (array_chunk($countries, $chunkLength) as $chunk) {
            $records = [];

            foreach ($chunk as $country) {
                $records[] = [
                    'name_en' => $country['country_enName'],
                    'name_ar' => $country['country_arName'],
                    'iso2' => $country['country_code'],
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Country::insert($records);
            $this->command?->getOutput()->progressAdvance(count($chunk));
        }

        $this->resetSequence('countries');
        $this->command?->getOutput()->progressFinish();
        $this->command?->info('Country Data Seeded has successful');
        $this->command?->line('');
    }

    protected function createStates(): void
    {
        $states = $this->getJsonFileAsArray('regions_lite');
        $countryId = $this->saudiArabiaId();
        $now = now();

        $this->command?->info('Starting Seed State Data ...');
        $this->command?->getOutput()->progressStart(count($states));

        $records = [];

        foreach ($states as $state) {
            $records[] = [
                'id' => $state['region_id'],
                'country_id' => $countryId,
                'name_en' => $state['name_en'],
                'name_ar' => $state['name_ar'],
                'code' => $state['code'] ?? null,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'meta' => json_encode([
                    'capital_city_id' => $state['capital_city_id'] ?? null,
                    'population' => $state['population'] ?? null,
                ]),
            ];

            $this->command?->getOutput()->progressAdvance();
        }

        State::insert($records);
        $this->resetSequence('states');

        $this->command?->getOutput()->progressFinish();
        $this->command?->info('State Data Seeded has successful');
        $this->command?->line('');
    }

    protected function createCities(): void
    {
        $cities = $this->getJsonFileAsArray('cities_lite');
        $chunkLength = 500;
        $countryId = $this->saudiArabiaId();
        $now = now();

        $this->command?->info('Starting Seed City Data ...');
        $this->command?->getOutput()->progressStart(count($cities));

        foreach (array_chunk($cities, $chunkLength) as $chunk) {
            $records = [];

            foreach ($chunk as $city) {
                $records[] = [
                    'id' => $city['city_id'],
                    'country_id' => $countryId,
                    'state_id' => $city['region_id'],
                    'name_en' => $city['name_en'],
                    'name_ar' => $city['name_ar'],
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            City::insert($records);
            $this->command?->getOutput()->progressAdvance(count($chunk));
        }

        $this->resetSequence('cities');

        $this->command?->getOutput()->progressFinish();
        $this->command?->info('City Data Seeded has successful');
    }

    protected function createSaVillages(): void
    {
        $rows = $this->getJsonFileAsArray('sa_villages');
        $chunkLength = 200;
        $countryId = $this->saudiArabiaId();
        $now = now();

        $this->command?->info('Starting Seed Saudi Villages Data ...');
        $this->command?->getOutput()->progressStart(count($rows));

        foreach (array_chunk($rows, $chunkLength) as $chunk) {
            $records = [];

            foreach ($chunk as $row) {
                $records[] = [
                    'id' => $row['villageId'],
                    'name_en' => $row['villageNameEng'],
                    'name_ar' => $row['villageNameArb'],
                    'country_id' => $countryId,
                    'state_id' => (int) ltrim((string) $row['regionCode'], '0') ?: null,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'meta' => json_encode($row),
                ];
            }

            Village::insert($records);
            $this->command?->getOutput()->progressAdvance(count($chunk));
        }

        $this->resetSequence('villages');

        $this->command?->getOutput()->progressFinish();
        $this->command?->info('Saudi villages Data Seeded has successful');
    }

    protected function createNeighborhoods(): void
    {
        $rows = $this->getJsonFileAsArray('districts_lite');
        $chunkLength = 500;
        $countryId = $this->saudiArabiaId();
        $now = now();

        $this->command?->info('Starting Seed Neighborhoods Data ...');
        $this->command?->getOutput()->progressStart(count($rows));

        foreach (array_chunk($rows, $chunkLength) as $chunk) {
            $records = [];

            foreach ($chunk as $row) {
                $records[] = [
                    'id' => $row['district_id'],
                    'name_en' => $row['name_en'],
                    'name_ar' => $row['name_ar'],
                    'country_id' => $countryId,
                    'state_id' => $row['region_id'],
                    'city_id' => $row['city_id'],
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Neighborhood::insert($records);
            $this->command?->getOutput()->progressAdvance(count($chunk));
        }

        $this->resetSequence('neighborhoods');

        $this->command?->getOutput()->progressFinish();
        $this->command?->info('Neighborhoods Data Seeded has successful');
    }

    protected function createLanguages(): void
    {
        $rows = $this->getJsonFileAsArray('languages');
        $chunkLength = 200;

        $this->command?->info('Starting Seed languages Data ...');
        $this->command?->getOutput()->progressStart(count($rows));

        foreach (array_chunk($rows, $chunkLength) as $chunk) {
            foreach ($chunk as $row) {
                Language::create([
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'name_native' => $row['name_native'] ?? null,
                    'dir' => $row['dir'] ?? 'ltr',
                ]);

                $this->command?->getOutput()->progressAdvance();
            }
        }

        $this->command?->getOutput()->progressFinish();
        $this->command?->info('languages Data Seeded has successful');
    }

    protected function createNationalities(): void
    {
        $rows = $this->getJsonFileAsArray('nationalities');
        $chunkLength = 200;
        $now = now();

        $this->command?->info('Starting Seed nationalities Data ...');
        $this->command?->getOutput()->progressStart(count($rows));

        foreach (array_chunk($rows, $chunkLength) as $chunk) {
            $records = [];

            foreach ($chunk as $row) {
                $records[] = [
                    'code' => $row['country_code'],
                    'name_en' => $row['country_enNationality'],
                    'name_ar' => $row['country_arNationality'],
                    'active' => true,
                    'meta' => json_encode([
                        'country_enName' => $row['country_enName'] ?? null,
                        'country_arName' => $row['country_arName'] ?? null,
                    ]),
                ];
            }

            Nationality::insert($records);
            $this->command?->getOutput()->progressAdvance(count($chunk));
        }

        $this->resetSequence('nationalities');

        $this->command?->getOutput()->progressFinish();
        $this->command?->info('nationalities Data Seeded has successful');
    }

    private function resetSequence(string $table): void
    {
        DB::connection('world')->statement(
            "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1))"
        );
    }
}

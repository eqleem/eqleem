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

class WorldSeeder extends Seeder
{
    protected $connection = 'world';

    public function run(): void
    {
        ini_set('memory_limit', '512M');

        if (\DB::connection()->getName() == 'world') {
            $this->createLanguages();
            $this->createNationalities();
            $this->createCountries();
            $this->createStates();
            $this->createCities();
            $this->createSaVillages();
            $this->createSaNeighborhoods();
        }
    }

    private static function getJsonFileAsArray(string $fileName)
    {
        $data = \File::get(__DIR__."/../data/$fileName.json");
        if (! $data) {
            return [];
        }

        return json_decode($data);
    }

    protected function createCountries(): void
    {
        $countries = $this->getJsonFileAsArray('countries');
        $chunkLength = 50;

        $this->command->info('Starting Seed Country Data ...');
        $this->command->getOutput()->progressStart(count($countries));

        foreach (array_chunk($countries, $chunkLength) as $chunk) {
            foreach ($chunk as $country) {

                Country::create([
                    'id' => $country->id,
                    'name' => $country->name,
                    'iso2' => $country->iso2,
                    'iso3' => $country->iso3,
                    'numeric_code' => $country->numeric_code,
                    'phonecode' => \Str::of($country->phonecode)->remove('+')->before('-')->prepend('+')->value(),
                    'capital' => $country->capital,
                    'currency' => $country->currency,
                    'currency_name' => $country->currency_name,
                    'currency_symbol' => $country->currency_symbol,
                    'tld' => $country->tld,
                    'native' => $country->native,
                    'region' => $country->region,
                    'subregion' => $country->subregion,
                    'timezones' => $country->timezones,
                    'translations' => $country->translations,
                    'latitude' => $country->latitude,
                    'longitude' => $country->longitude,
                    'emoji' => $country->emoji,
                    'emojiU' => $country->emojiU,
                    'flag' => $country->flag,
                    'iso2' => $country->iso2,
                    'iso3' => $country->iso3,
                    // 'active' => $this->serves->isCountryActiveByIso2OrIso3(
                    // iso2: $country->iso2,
                    // iso3: $country->iso3
                    // ),
                ]);
            }
            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Country Data Seeded has successful');
        $this->command->line('');
    }

    protected function createStates(): void
    {
        $states = $this->getJsonFileAsArray('states');
        $chunkLength = 50;

        $this->command->info('Starting Seed State Data ...');
        $this->command->getOutput()->progressStart(count($states));

        foreach (array_chunk($states, $chunkLength) as $chunk) {
            foreach ($chunk as $state) {
                State::create([
                    'id' => $state->id,
                    'name' => $state->name,
                    'country_id' => $state->country_id,
                    'latitude' => $state->latitude,
                    'longitude' => $state->longitude,
                    // 'active' => $this->serves->isStateActiveByCountryId(
                    //     countryId: $state->country_id
                    // ),
                ]);
                $this->command->getOutput()->progressAdvance();
            }
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('State Data Seeded has successful');
        $this->command->line('');
    }

    protected function createCities(): void
    {
        $cities = $this->getJsonFileAsArray('cities');
        $chunkLength = 500;
        $now = now();

        $this->command->info('Starting Seed City Data ...');
        $this->command->getOutput()->progressStart(count($cities));

        foreach (array_chunk($cities, $chunkLength) as $chunk) {
            $records = [];

            foreach ($chunk as $city) {
                $records[] = [
                    'id' => $city->id,
                    'name' => $city->name,
                    'country_id' => $city->country_id,
                    'state_id' => $city->state_id,
                    'latitude' => $city->latitude,
                    'longitude' => $city->longitude,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            City::insert($records);
            $this->command->getOutput()->progressAdvance(count($chunk));
        }

        unset($cities);

        $this->command->getOutput()->progressFinish();
        $this->command->info('City Data Seeded has successful');
    }

    protected function getSaudiStateId($region)
    {
        $map = [
            'Region 1' => 'Riyadh',
            'Region 2' => 'Makkah',
            'Region 3' => 'Al Madinah',
            'Region 4' => 'Al-Qassim',
            'Region 5' => 'Eastern Province',
            'Region 6' => '\'Asir',
            'Region 7' => 'Tabuk',
            'Region 8' => 'Ha\'il',
            'Region 9' => 'Northern Borders',
            'Region 10' => 'Jizan',
            'Region 11' => 'Najran',
            'Region 12' => 'Al Bahah',
            'Region 13' => 'Al Jawf',
        ];

        return State::select('id', 'name')->whereName($map[$region])->first()?->id;
    }

    protected function createSaVillages(): void
    {
        $rows = $this->getJsonFileAsArray('sa_villages');
        $chunkLength = 200;

        $this->command->info('Starting Seed Saudi Villages Data ...');
        $this->command->getOutput()->progressStart(count($rows));

        foreach (array_chunk($rows, $chunkLength) as $chunk) {
            foreach ($chunk as $row) {
                Village::create([
                    'name' => data_get($row, 'villageNameEng'),
                    'translations' => [
                        'ar' => data_get($row, 'villageNameArb'),
                        'en' => data_get($row, 'villageNameEng'),
                    ],
                    'country_id' => 194, // saudi arabia
                    'state_id' => $this->getSaudiStateId(data_get($row, 'regionNameEng'), 0),
                    'meta' => collect($row)->toArray(),
                ]);

                $this->command->getOutput()->progressAdvance();
            }
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Saudi villages Data Seeded has successful');
    }

    protected function getSaudiCityId($region)
    {
        $map = [];

        // return City::select('id', 'name')->whereName($map[$region])->first()?->id;
    }

    protected function createSaNeighborhoods(): void
    {
        $rows = $this->getJsonFileAsArray('sa_neighborhoods');
        $chunkLength = 200;

        $this->command->info('Starting Seed Saudi Neighborhoods Data ...');
        $this->command->getOutput()->progressStart(count($rows));

        foreach (array_chunk($rows, $chunkLength) as $chunk) {
            foreach ($chunk as $row) {
                Neighborhood::create([
                    'name' => data_get($row, 'nameEn'),
                    'translations' => [
                        'ar' => data_get($row, 'nameAr'),
                        'en' => data_get($row, 'nameEn'),
                    ],
                    'country_id' => 194, // saudi arabia
                    // 'city_id' => $this->getSaudiCityId(data_get($row, 'cityId'), 0), // #TODO LATER
                    'meta' => collect($row)->toArray(),
                ]);

                $this->command->getOutput()->progressAdvance();
            }
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Saudi Neighborhoods Data Seeded has successful');
    }

    protected function createLanguages(): void
    {
        $rows = $this->getJsonFileAsArray('languages');
        $chunkLength = 200;

        $this->command->info('Starting Seed languages Data ...');
        $this->command->getOutput()->progressStart(count($rows));

        foreach (array_chunk($rows, $chunkLength) as $chunk) {
            foreach ($chunk as $row) {
                Language::create([
                    'code' => data_get($row, 'code'),
                    'name' => data_get($row, 'name'),
                    'name_native' => data_get($row, 'name_native'),
                    'dir' => data_get($row, 'dir'),
                ]);

                $this->command->getOutput()->progressAdvance();
            }
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('languages Data Seeded has successful');
    }

    protected function createNationalities(): void
    {
        $rows = $this->getJsonFileAsArray('nationalities');
        $chunkLength = 200;

        $this->command->info('Starting Seed nationalities Data ...');
        $this->command->getOutput()->progressStart(count($rows));

        foreach (array_chunk($rows, $chunkLength) as $chunk) {
            foreach ($chunk as $row) {
                Nationality::create([
                    'code' => data_get($row, 'country_code'),
                    'name' => data_get($row, 'country_enNationality'),
                    'translations' => [
                        'ar' => data_get($row, 'country_arNationality'),
                        'en' => data_get($row, 'country_enNationality'),
                    ],
                ]);

                $this->command->getOutput()->progressAdvance();
            }
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('nationalities Data Seeded has successful');
    }
}

<?php

use App\Models\Language;
use Database\Seeders\WorldSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function worldDatabaseIsAvailable(): bool
{
    try {
        DB::connection('world')->getPdo();

        return Schema::connection('world')->hasTable('languages');
    } catch (Throwable) {
        return false;
    }
}

function invokeWorldSeederMethod(string $method): void
{
    $seeder = new WorldSeeder;
    $reflection = new ReflectionMethod(WorldSeeder::class, $method);
    $reflection->setAccessible(true);
    $reflection->invoke($seeder);
}

beforeEach(function () {
    if (! worldDatabaseIsAvailable()) {
        $this->markTestSkipped('World database is not available.');
    }

    config(['database.default' => 'world']);

    Language::query()->delete();
});

it('skips existing language codes when seeding languages', function () {
    Language::query()->create([
        'code' => 'ab',
        'name' => 'Abkhazian',
        'name_native' => 'аҧсуа',
        'dir' => 'ltr',
        'active' => true,
    ]);

    invokeWorldSeederMethod('createLanguages');

    expect(Language::query()->count())->toBe(183)
        ->and(Language::query()->where('code', 'ab')->value('name'))->toBe('Abkhazian');
});

it('can seed languages from an empty table', function () {
    invokeWorldSeederMethod('createLanguages');

    expect(Language::query()->count())->toBe(183)
        ->and(Language::query()->where('code', 'ar')->exists())->toBeTrue();
});

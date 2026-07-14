<?php

use App\Models\Block;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantPageBlocks;
use Database\Seeders\ThemeSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
});

it('loads shell blocks without querying home page blocks', function () {
    $owner = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Page Blocks Cache',
        'handle' => 'page-blocks-'.Str::lower(Str::random(6)),
        'user_id' => $owner->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $cache = app(TenantPageBlocks::class);
    $cache->flush();

    DB::flushQueryLog();
    DB::enableQueryLog();

    $header = $cache->singleton('header');
    $cta = $cache->singleton('cta');
    $footer = $cache->singleton('footer');

    $queries = collect(DB::getQueryLog());
    $blockQueries = $queries->filter(fn (array $query): bool => str_contains(strtolower($query['query']), 'from "blocks"')
        || str_contains(strtolower($query['query']), 'from `blocks`')
        || str_contains(strtolower($query['query']), 'from blocks'));

    expect($blockQueries)->toHaveCount(1)
        ->and($header)->not->toBeNull()
        ->and($cta)->not->toBeNull()
        ->and($footer)->not->toBeNull();

    $shellSql = strtolower((string) $blockQueries->first()['query']);

    expect($shellSql)->toContain('type')
        ->and($shellSql)->not->toContain('position');

    expect($cta->relationLoaded('contents'))->toBeTrue();

    $cta->activeContents('cta-link');

    $contentQueries = collect(DB::getQueryLog())->filter(
        fn (array $query): bool => str_contains(strtolower($query['query']), 'from "contents"')
            || str_contains(strtolower($query['query']), 'from `contents`')
            || str_contains(strtolower($query['query']), 'from contents')
    );

    expect($contentQueries)->toHaveCount(1)
        ->and($cta->activeContents('cta-link'))->toBeInstanceOf(Collection::class)
        ->and($header->contents)->toHaveCount(0);

    $bindings = collect($contentQueries->first()['bindings'] ?? []);
    $blockIdBindings = $bindings
        ->take(2)
        ->map(fn (mixed $value): int => (int) $value)
        ->all();

    expect($blockIdBindings)->toEqualCanonicalizing([(int) $cta->id, (int) $footer->id]);
});

it('loads home page blocks in a separate query when requested', function () {
    $owner = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Page Blocks Cache',
        'handle' => 'page-blocks-'.Str::lower(Str::random(6)),
        'user_id' => $owner->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $cache = app(TenantPageBlocks::class);
    $cache->flush();
    $cache->singleton('header');

    DB::flushQueryLog();
    DB::enableQueryLog();

    $homeBlocks = $cache->homeBlocks();

    $blockQueries = collect(DB::getQueryLog())->filter(fn (array $query): bool => str_contains(strtolower($query['query']), 'from "blocks"')
        || str_contains(strtolower($query['query']), 'from `blocks`')
        || str_contains(strtolower($query['query']), 'from blocks'));

    expect($blockQueries)->toHaveCount(1)
        ->and($homeBlocks->where('type', 'block-link')->count())->toBeGreaterThan(0)
        ->and(Block::homePageBlocks())->toHaveCount($homeBlocks->count());

    $firstLink = $homeBlocks->firstWhere('type', 'block-link');

    expect($firstLink)->not->toBeNull()
        ->and($cache->pageBlock((int) $firstLink->id, ['block-link', 'link']))->not->toBeNull();
});

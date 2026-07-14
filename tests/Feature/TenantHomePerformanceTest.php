<?php

use App\Livewire\Tenant\Blocks\HomePageBlocks;
use App\Livewire\Tenant\Blocks\PagesMenu;
use App\Livewire\Tenant\Cart\Badge;
use App\Models\Block;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use App\Support\TenantPageBlocks;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
});

/**
 * @return array{0: User, 1: Tenant}
 */
function createTenantForHomePerformance(): array
{
    $owner = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $theme = Theme::query()->where('slug', 'default')->firstOrFail();

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Home Perf Tenant',
        'handle' => 'home-perf-'.Str::lower(Str::random(6)),
        'user_id' => $owner->id,
        'theme_id' => $theme->id,
        'active' => true,
        'status' => 'active',
        'meta' => [
            'contact_saved' => true,
            'logo_saved' => true,
            'contact' => [
                'phone' => '',
                'email' => '',
                'whatsapp' => '',
                'country' => 'السعودية',
                'city' => 'الرياض',
            ],
            'social_links' => [],
            'brand_mark' => [
                'type' => 'emoji',
                'value' => '🏠',
                'color' => '',
            ],
        ],
    ]);

    setCurrentTenant($tenant);

    $tenant->themes()->syncWithoutDetaching([
        $theme->id => [
            'active' => true,
            'meta' => [
                'primaryColor' => 'blue',
                'bgColor' => 'stone',
            ],
        ],
    ]);

    return [$owner, $tenant->fresh()];
}

it('loads the tenant home shell without querying home block links or users', function () {
    [, $tenant] = createTenantForHomePerformance();

    app(TenantPageBlocks::class)->flush();

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->get(route('tenant.home', ['tenant' => $tenant->handle]))
        ->assertSuccessful();

    $queries = collect(DB::getQueryLog());
    $sql = $queries->pluck('query')->map(fn (string $query): string => strtolower($query));

    $blockSelects = $sql->filter(fn (string $query): bool => str_contains($query, 'from "blocks"')
        || str_contains($query, 'from `blocks`')
        || str_contains($query, 'from blocks'))->values();

    $homeBlockSelects = $blockSelects->filter(fn (string $query): bool => str_contains($query, 'position'));

    expect($queries->count())->toBeLessThanOrEqual(12)
        ->and($blockSelects)->toHaveCount(1)
        ->and($homeBlockSelects)->toHaveCount(0)
        ->and($sql->contains(fn (string $query): bool => str_contains($query, 'from "users"')
            || str_contains($query, 'from `users`')
            || str_contains($query, 'from users')))->toBeFalse();
});

it('lazy home page blocks render the seeded block link titles', function () {
    [, $tenant] = createTenantForHomePerformance();
    setCurrentTenant($tenant);

    view()->prependNamespace('tenant-theme', public_path('themes/default'));
    view()->prependNamespace('default-tenant-theme', public_path('themes/default'));

    $links = Block::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->where('type', 'block-link')
        ->where('position', 'home')
        ->where('active', true)
        ->orderBy('sort_order')
        ->limit(3)
        ->get();

    expect($links)->not->toBeEmpty();

    $component = Livewire::test(HomePageBlocks::class)->assertSuccessful();

    foreach ($links as $link) {
        $title = (string) data_get($link->data, 'title', '');

        if ($title !== '') {
            $component->assertSee($title, false);
        }
    }
});

it('lazy pages menu and cart badge render independently', function () {
    [, $tenant] = createTenantForHomePerformance();
    setCurrentTenant($tenant);

    Livewire::test(PagesMenu::class)->assertSuccessful();
    Livewire::test(Badge::class)->assertSuccessful();
});

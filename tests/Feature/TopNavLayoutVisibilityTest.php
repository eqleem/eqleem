<?php

use App\Actions\CreateDefaultBlocks;
use App\Models\Tenant;
use App\Models\Theme;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);

    view()->prependNamespace('tenant-theme', public_path('themes/default'));
    view()->prependNamespace('default-tenant-theme', public_path('themes/default'));
});

function createTenantForTopNavLayoutVisibility(): Tenant
{
    $owner = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $theme = Theme::query()->where('slug', 'default')->firstOrFail();

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Top Nav Visibility Tenant',
        'handle' => 'top-nav-vis-'.Str::lower(Str::random(6)),
        'user_id' => $owner->id,
        'theme_id' => $theme->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    return $tenant->fresh();
}

it('hides the layout top nav on the tenant home page', function () {
    $tenant = createTenantForTopNavLayoutVisibility();

    $html = $this->get(route('tenant.home', ['tenant' => $tenant->handle]))
        ->assertSuccessful()
        ->getContent();

    $beforeMain = Str::before($html, '<main');

    expect($beforeMain)->not->toContain('wire:name="tenant.blocks.top-nav"');
});

it('shows the layout top nav on non-home tenant pages', function () {
    $tenant = createTenantForTopNavLayoutVisibility();

    $html = $this->get(route('tenant.blog.index', ['tenant' => $tenant->handle]))
        ->assertSuccessful()
        ->getContent();

    $beforeMain = Str::before($html, '<main');

    expect($beforeMain)->toContain('wire:name="tenant.blocks.top-nav"');
});

<?php

use App\Livewire\Tenant\Courses\Index as CoursesIndex;
use App\Livewire\Tenant\Portfolio\Index as PortfolioIndex;
use App\Livewire\Tenant\Services\Index as ServicesIndex;
use App\Livewire\Tenant\Store\Index as StoreIndex;
use App\Models\Content;
use App\Models\Taxonomy;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);

    view()->prependNamespace('tenant-theme', public_path('themes/default'));
    view()->prependNamespace('default-tenant-theme', public_path('themes/default'));
});

/**
 * @return array{0: Tenant, 1: string}
 */
function createModuleIndexTenant(string $contentTypeSlug): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Module Filters Tenant',
        'handle' => 'module-filters-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel($contentTypeSlug),
        'title' => 'عنصر تجريبي',
        'slug' => 'demo-item-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'published_at' => now()->subMinute(),
        'active' => true,
        'data' => ['price' => 1000],
    ]);

    setCurrentTenant($tenant);

    return [$tenant, contentTypeModel($contentTypeSlug)];
}

dataset('module_index_filters', [
    'store' => [StoreIndex::class, 'store', 'store_category'],
    'services' => [ServicesIndex::class, 'services', 'service_category'],
    'portfolio' => [PortfolioIndex::class, 'portfolio', 'portfolio_category'],
    'courses' => [CoursesIndex::class, 'courses', 'course_category'],
]);

it('hides category filters and search when there are no categories', function (string $component, string $contentType, string $taxonomyType) {
    createModuleIndexTenant($contentType);

    Livewire::test($component)
        ->assertDontSee('الكل')
        ->assertDontSee('aria-label="البحث"', false);
})->with('module_index_filters');

it('shows category filters and search when categories exist', function (string $component, string $contentType, string $taxonomyType) {
    [$tenant] = createModuleIndexTenant($contentType);

    Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'تصنيف تجريبي',
        'type' => $taxonomyType,
        'sort_order' => 1,
    ]);

    Livewire::test($component)
        ->assertSee('الكل')
        ->assertSee('تصنيف تجريبي')
        ->assertSeeHtml('aria-label="البحث"');
})->with('module_index_filters');

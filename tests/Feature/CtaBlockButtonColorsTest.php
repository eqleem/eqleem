<?php

use App\Livewire\Tenant\Blocks\Cta;
use App\Models\Block;
use App\Models\Content;
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
 * @return array{0: Tenant, 1: Block}
 */
function createTenantWithCtaBlock(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'CTA Colors Tenant',
        'handle' => 'cta-colors-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $block = Block::findSingleton('cta');
    expect($block)->not->toBeNull();

    Content::query()
        ->where('block_id', $block->id)
        ->type('cta-link')
        ->delete();

    return [$tenant, $block];
}

function createExternalCtaLink(Block $block, Tenant $tenant, string $label, string $url, int $sortOrder): Content
{
    return Content::query()->create([
        'tenant_id' => $tenant->id,
        'block_id' => $block->id,
        'type' => 'cta-link',
        'title' => $label,
        'slug' => 'cta-'.Str::lower(Str::random(8)),
        'data' => [
            'link_type' => 'external',
            'content_type' => null,
            'label' => $label,
            'url' => $url,
            'icon' => null,
            'content_id' => null,
        ],
        'sort_order' => $sortOrder,
        'active' => true,
        'status' => 'published',
        'published_at' => now(),
    ]);
}

it('uses primary color for the first cta button and secondary for the rest', function () {
    [$tenant, $block] = createTenantWithCtaBlock();

    createExternalCtaLink($block, $tenant, 'الزر الأول', 'https://example.com/one', 1);
    createExternalCtaLink($block, $tenant, 'الزر الثاني', 'https://example.com/two', 2);
    createExternalCtaLink($block, $tenant, 'الزر الثالث', 'https://example.com/three', 3);

    $html = Livewire::test(Cta::class)
        ->assertSuccessful()
        ->assertSee('الزر الأول', false)
        ->assertSee('الزر الثاني', false)
        ->assertSee('الزر الثالث', false)
        ->html();

    expect(substr_count($html, 'bg-primary-600'))->toBe(1)
        ->and(substr_count($html, 'bg-secondary-900/10'))->toBe(2)
        ->and(substr_count($html, 'text-secondary-900'))->toBe(2);

    $firstPos = strpos($html, 'الزر الأول');
    $secondPos = strpos($html, 'الزر الثاني');
    $thirdPos = strpos($html, 'الزر الثالث');

    expect($firstPos)->not->toBeFalse()
        ->and($secondPos)->not->toBeFalse()
        ->and($thirdPos)->not->toBeFalse()
        ->and($firstPos)->toBeLessThan($secondPos)
        ->and($secondPos)->toBeLessThan($thirdPos);

    $primaryBeforeFirst = strrpos(substr($html, 0, $firstPos), 'bg-primary-600');
    $secondaryBeforeSecond = strrpos(substr($html, 0, $secondPos), 'bg-secondary-900/10');
    $secondaryBeforeThird = strrpos(substr($html, 0, $thirdPos), 'bg-secondary-900/10');

    expect($primaryBeforeFirst)->not->toBeFalse()
        ->and($secondaryBeforeSecond)->not->toBeFalse()
        ->and($secondaryBeforeThird)->not->toBeFalse();
});

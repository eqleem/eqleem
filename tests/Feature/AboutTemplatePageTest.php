<?php

use App\Livewire\Tenant\Page\Detail as PageDetail;
use App\Livewire\Tenant\Pages\About;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Support\AboutPageView;
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
 * @return array{0: Tenant, 1: Content}
 */
function createTenantWithAboutPage(array $dataOverrides = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'About Tenant',
        'handle' => 'about-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $page = Content::query()
        ->type(contentTypeModel('pages'))
        ->template('about')
        ->firstOrFail();

    $page->update([
        'title' => 'من نحن',
        'status' => 'published',
        'published_at' => now(),
        'active' => true,
        'data' => array_merge(Content::defaultAboutPageData(), $dataOverrides),
    ]);

    return [$tenant->fresh(), $page->fresh()];
}

it('renders page.about for content pages with the about template', function () {
    [, $page] = createTenantWithAboutPage([
        'subtitle' => 'قصتنا باختصار',
        'stats' => [
            ['id' => 's1', 'value' => '95%', 'label' => 'رضا العملاء'],
        ],
        'features_title' => 'مزايانا',
        'features' => [
            [
                'id' => 'f1',
                'title' => 'سرعة الإنجاز',
                'description' => 'ننجز بسرعة وجودة.',
                'brand_mark' => [
                    'type' => 'emoji',
                    'value' => '⚡',
                    'color' => '',
                ],
            ],
        ],
        'primary_button' => [
            'label' => 'تواصل معنا',
            'link_type' => 'external',
            'content_type' => null,
            'content_id' => null,
            'url' => 'https://example.com/contact',
            'branch_ids' => [],
            'calendar_ids' => [],
            'allow_client_choice' => true,
            'duration_minutes' => 30,
        ],
    ]);

    Livewire::test(PageDetail::class, ['slug' => $page->slug])
        ->assertSuccessful()
        ->assertSee('من نحن', false)
        ->assertSee('قصتنا باختصار', false)
        ->assertSee('95%', false)
        ->assertSee('رضا العملاء', false)
        ->assertSee('مزايانا', false)
        ->assertSee('سرعة الإنجاز', false)
        ->assertSee('تواصل معنا', false)
        ->assertDontSee('شارة', false);
});

it('renders page.about from the dedicated about route', function () {
    createTenantWithAboutPage([
        'features' => [
            [
                'id' => 'f1',
                'title' => 'ميزة المسار المباشر',
                'description' => 'وصف المسار',
                'brand_mark' => null,
            ],
        ],
    ]);

    Livewire::test(About::class)
        ->assertSuccessful()
        ->assertSee('ميزة المسار المباشر', false)
        ->assertSee('وصف المسار', false);
});

it('builds about view data from page settings', function () {
    [, $page] = createTenantWithAboutPage([
        'subtitle' => 'وصف من نحن',
        'stats' => [
            ['id' => 'keep', 'value' => '10+', 'label' => 'سنوات'],
            ['id' => 'skip', 'value' => '', 'label' => 'فارغ'],
        ],
        'features' => [
            [
                'id' => 'keep',
                'title' => 'ميزة صالحة',
                'description' => 'وصف',
                'brand_mark' => null,
            ],
            [
                'id' => 'skip',
                'title' => '',
                'description' => 'بدون عنوان',
                'brand_mark' => null,
            ],
        ],
        'primary_button' => [
            'label' => '',
            'link_type' => 'external',
            'url' => 'https://example.com',
        ],
    ]);

    $payload = AboutPageView::make($page);

    expect($payload['subtitle'])->toBe('وصف من نحن')
        ->and($payload['stats'])->toHaveCount(1)
        ->and($payload['stats'][0]['value'])->toBe('10+')
        ->and($payload['features'])->toHaveCount(1)
        ->and($payload['features'][0]['title'])->toBe('ميزة صالحة')
        ->and($payload['primaryButton'])->toBeNull();
});

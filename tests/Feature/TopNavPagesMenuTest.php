<?php

use App\Livewire\Tenant\Blocks\TopNav;
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
 * @return array{0: Tenant, 1: Content, 2: Content, 3: Content}
 */
function createTenantWithPublishedPagesForTopNav(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Top Nav Tenant',
        'handle' => 'topnav-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $contact = Content::query()
        ->type(contentTypeModel('pages'))
        ->template('contact')
        ->firstOrFail();

    $faq = Content::query()
        ->type(contentTypeModel('pages'))
        ->template('faq')
        ->firstOrFail();

    $custom = Content::query()->create([
        'uuid' => (string) Str::uuid(),
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('pages'),
        'template' => 'default',
        'title' => 'صفحة مخصصة طويلة الاسم لاختبار القص',
        'slug' => 'custom-page-'.Str::lower(Str::random(4)),
        'status' => 'published',
        'published_at' => now(),
        'active' => true,
        'sort_order' => 3,
    ]);

    foreach ([$contact, $faq] as $page) {
        $page->update([
            'status' => 'published',
            'published_at' => now(),
            'active' => true,
        ]);
    }

    $contact->update(['title' => 'اتصل بنا', 'sort_order' => 1]);
    $faq->update(['title' => 'الأسئلة المتكررة', 'sort_order' => 2]);

    return [$tenant->fresh(), $contact->fresh(), $faq->fresh(), $custom->fresh()];
}

it('maps page menu icons by template', function () {
    expect(TopNav::pageMenuIcon('contact'))->toBe('hugeicons:call')
        ->and(TopNav::pageMenuIcon('faq'))->toBe('hugeicons:help-circle')
        ->and(TopNav::pageMenuIcon('features'))->toBe('hugeicons:magic-wand-02')
        ->and(TopNav::pageMenuIcon('pricing'))->toBe('hugeicons:credit-card-change')
        ->and(TopNav::pageMenuIcon('default'))->toBe('hugeicons:file-01')
        ->and(TopNav::pageMenuIcon(null))->toBe('hugeicons:file-01');
});

it('renders published pages with template icons and end-aligned dropdown', function () {
    [, $contact, $faq, $custom] = createTenantWithPublishedPagesForTopNav();

    Livewire::test(TopNav::class)
        ->assertSuccessful()
        ->assertSee('اتصل بنا', false)
        ->assertSee('الأسئلة المتكررة', false)
        ->assertSee($custom->title, false)
        ->assertSee('hugeicons:call', false)
        ->assertSee('hugeicons:help-circle', false)
        ->assertSee('hugeicons:file-01', false)
        ->assertSee('absolute end-0', false)
        ->assertSee('w-56', false)
        ->assertSeeHtml('min-w-0 truncate')
        ->assertSee(route('tenant.page.detail', $contact->slug), false)
        ->assertSee(route('tenant.page.detail', $faq->slug), false)
        ->assertSee(route('tenant.page.detail', $custom->slug), false);
});

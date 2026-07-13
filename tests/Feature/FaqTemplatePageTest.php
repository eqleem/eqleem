<?php

use App\Livewire\Tenant\Page\Detail as PageDetail;
use App\Livewire\Tenant\Pages\Faq;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Support\FaqPageView;
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
function createTenantWithFaqPage(array $dataOverrides = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'FAQ Tenant',
        'handle' => 'faq-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $page = Content::query()
        ->type(contentTypeModel('pages'))
        ->template('faq')
        ->firstOrFail();

    $page->update([
        'title' => 'الأسئلة المتكررة',
        'status' => 'published',
        'published_at' => now(),
        'active' => true,
        'data' => array_merge(Content::defaultFaqPageData(), $dataOverrides),
    ]);

    return [$tenant->fresh(), $page->fresh()];
}

it('renders page.faq for content pages with the faq template', function () {
    [, $page] = createTenantWithFaqPage([
        'subtitle' => 'أجوبة سريعة لأسئلتك',
        'faqs' => [
            [
                'id' => 'q1',
                'question' => 'كيف أبدأ؟',
                'answer' => 'من لوحة التحكم.',
            ],
            [
                'id' => 'q2',
                'question' => 'هل يوجد دعم؟',
                'answer' => 'نعم، على مدار الساعة.',
            ],
        ],
    ]);

    Livewire::test(PageDetail::class, ['slug' => $page->slug])
        ->assertSuccessful()
        ->assertSee('الأسئلة المتكررة', false)
        ->assertSee('أجوبة سريعة لأسئلتك', false)
        ->assertSee('كيف أبدأ؟', false)
        ->assertSee('من لوحة التحكم.', false)
        ->assertSee('هل يوجد دعم؟', false)
        ->assertSee('تواصل معنا', false);
});

it('renders page.faq from the dedicated faq route', function () {
    createTenantWithFaqPage([
        'faqs' => [
            [
                'id' => 'q1',
                'question' => 'سؤال من المسار المباشر',
                'answer' => 'جواب المسار المباشر',
            ],
        ],
    ]);

    Livewire::test(Faq::class)
        ->assertSuccessful()
        ->assertSee('سؤال من المسار المباشر', false)
        ->assertSee('جواب المسار المباشر', false);
});

it('shows an empty state when no faqs exist', function () {
    [, $page] = createTenantWithFaqPage([
        'faqs' => [],
    ]);

    Livewire::test(PageDetail::class, ['slug' => $page->slug])
        ->assertSuccessful()
        ->assertSee('لا توجد أسئلة متكررة بعد.', false);
});

it('builds faq view data from page settings', function () {
    [, $page] = createTenantWithFaqPage([
        'subtitle' => 'وصف الأسئلة',
        'faqs' => [
            [
                'id' => 'keep',
                'question' => 'سؤال صالح',
                'answer' => 'جواب',
            ],
            [
                'id' => 'skip',
                'question' => '',
                'answer' => 'بدون سؤال',
            ],
        ],
    ]);

    $payload = FaqPageView::make($page);

    expect($payload['subtitle'])->toBe('وصف الأسئلة')
        ->and($payload['faqs'])->toHaveCount(1)
        ->and($payload['faqs'][0]['question'])->toBe('سؤال صالح');
});

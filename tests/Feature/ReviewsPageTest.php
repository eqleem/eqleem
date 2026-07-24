<?php

use App\Livewire\Tenant\Pages\Reviews;
use App\Models\Client;
use App\Models\Review;
use App\Models\Setting;
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
 * @return array{0: Tenant}
 */
function createReviewsPageTenant(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'صفحة التقييمات',
        'handle' => 'reviews-page-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    return [$tenant];
}

test('reviews page shows real published reviews and hides fake sample copy', function () {
    [$tenant] = createReviewsPageTenant();

    Setting::saveForSlug(Setting::REVIEW_SETTINGS_SLUG, [
        'section_title' => 'تقييمات المتجر',
        'per_page' => 12,
    ]);

    Review::query()->create([
        'tenant_id' => $tenant->id,
        'title' => 'خدمة ممتازة وسرعة في الرد',
        'score' => 'تجربتي كانت رائعة من البداية حتى النهاية.',
        'rating' => 5,
        'name' => 'سارة',
        'published' => true,
    ]);

    Review::query()->create([
        'tenant_id' => $tenant->id,
        'title' => 'تقييم غير منشور',
        'score' => 'لا يجب أن يظهر هذا التقييم.',
        'rating' => 1,
        'name' => 'مخفي',
        'published' => false,
    ]);

    Livewire::test(Reviews::class)
        ->assertSuccessful()
        ->assertSee('تقييمات المتجر')
        ->assertSee('خدمة ممتازة وسرعة في الرد')
        ->assertSee('تجربتي كانت رائعة من البداية حتى النهاية.')
        ->assertSee('سارة')
        ->assertDontSee('أحمد القحطاني')
        ->assertDontSee('أبعاد البيت')
        ->assertDontSee('تقييم غير منشور')
        ->assertDontSee('لا يجب أن يظهر هذا التقييم.');
});

test('guest clicking add review opens login prompt and remembers intended url', function () {
    [$tenant] = createReviewsPageTenant();

    Livewire::test(Reviews::class)
        ->call('openAddReview')
        ->assertDispatched('open-modal', name: 'reviews-login-modal');

    expect(session('client_auth_intended'))->toBe(route('tenant.pages.reviews', [
        'tenant' => $tenant->handle,
    ]));
});

test('authenticated client can submit a review', function () {
    [$tenant] = createReviewsPageTenant();

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'tenant_id' => $tenant->id,
        'name' => 'عميل التقييم',
        'email' => 'reviewer@example.com',
        'active' => true,
    ]);
    $client->tenants()->attach($tenant->id, ['active' => true]);

    $this->actingAs($client, 'client');

    Livewire::test(Reviews::class)
        ->call('openAddReview')
        ->assertDispatched('open-modal', name: 'add-testimonial-modal')
        ->set('title', 'تجربة رائعة')
        ->set('score', 'أنصح الجميع بالتعامل معهم بكل ثقة.')
        ->set('rating', 5)
        ->call('submitReview')
        ->assertHasNoErrors()
        ->assertSee('تجربة رائعة')
        ->assertSee('أنصح الجميع بالتعامل معهم بكل ثقة.');

    expect(Review::query()->where('client_id', $client->id)->count())->toBe(1);
});

test('authenticated client edits existing general review instead of creating another', function () {
    [$tenant] = createReviewsPageTenant();

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'tenant_id' => $tenant->id,
        'name' => 'عميل التعديل',
        'email' => 'editor@example.com',
        'active' => true,
    ]);
    $client->tenants()->attach($tenant->id, ['active' => true]);

    $review = Review::query()->create([
        'tenant_id' => $tenant->id,
        'client_id' => $client->id,
        'content_id' => null,
        'title' => 'تقييمي الأول',
        'score' => 'نص التقييم الأول قبل التعديل هنا.',
        'rating' => 3,
        'name' => 'عميل التعديل',
        'published' => true,
    ]);

    $this->actingAs($client, 'client');

    Livewire::test(Reviews::class)
        ->assertSee('تعديل تقييمي')
        ->call('openAddReview')
        ->assertSet('editingReviewId', $review->id)
        ->assertSet('title', 'تقييمي الأول')
        ->assertSet('score', 'نص التقييم الأول قبل التعديل هنا.')
        ->assertSet('rating', 3)
        ->set('title', 'تقييمي بعد التعديل')
        ->set('score', 'تم تحديث نص التقييم بالكامل الآن.')
        ->set('rating', 5)
        ->call('submitReview')
        ->assertHasNoErrors()
        ->assertSee('تقييمي بعد التعديل')
        ->assertDontSee('تقييمي الأول');

    expect(Review::query()->where('client_id', $client->id)->count())->toBe(1)
        ->and($review->fresh()->title)->toBe('تقييمي بعد التعديل')
        ->and($review->fresh()->rating)->toBe(5);
});

test('reviews page respects configured per page setting', function () {
    [$tenant] = createReviewsPageTenant();

    Setting::saveForSlug(Setting::REVIEW_SETTINGS_SLUG, [
        'section_title' => 'التقييمات',
        'per_page' => 2,
    ]);

    foreach (range(1, 3) as $index) {
        Review::query()->create([
            'tenant_id' => $tenant->id,
            'title' => "تقييم رقم {$index}",
            'score' => "نص التقييم رقم {$index} للتأكد من التصفح.",
            'rating' => 4,
            'name' => "عميل {$index}",
            'published' => true,
            'created_at' => now()->subMinutes($index),
        ]);
    }

    Livewire::test(Reviews::class)
        ->assertSee('تقييم رقم 3')
        ->assertSee('تقييم رقم 2')
        ->assertDontSee('تقييم رقم 1');
});

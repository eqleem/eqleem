<?php

use App\Livewire\Tenant\Page\Detail as PageDetail;
use App\Livewire\Tenant\Pages\Contact;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use App\Support\ContactPageView;
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
function createTenantWithContactPage(array $dataOverrides = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Contact Tenant',
        'handle' => 'contact-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    app(TenantProfileService::class)->saveContact($tenant, [
        'phone' => '0501234567',
        'email' => 'hello@example.com',
        'whatsapp' => '966501234567',
    ]);

    $page = Content::query()
        ->type(contentTypeModel('pages'))
        ->template('contact')
        ->firstOrFail();

    $page->update([
        'title' => 'اتصل بنا',
        'status' => 'published',
        'published_at' => now(),
        'active' => true,
        'data' => array_merge(Content::defaultContactPageData(), $dataOverrides),
    ]);

    return [$tenant->fresh(), $page->fresh()];
}

it('renders page.contact for content pages with the contact template', function () {
    [, $page] = createTenantWithContactPage([
        'subtitle' => 'تواصل معنا عبر النموذج',
    ]);

    Livewire::test(PageDetail::class, ['slug' => $page->slug])
        ->assertSuccessful()
        ->assertSee('نموذج التواصل', false)
        ->assertSee('بيانات التواصل', false)
        ->assertSee('hello@example.com', false)
        ->assertSee('إرسال الرسالة', false)
        ->assertSee('الأسئلة المتكررة', false);
});

it('renders page.contact from the dedicated contact route', function () {
    createTenantWithContactPage();

    Livewire::test(Contact::class)
        ->assertSuccessful()
        ->assertSee('نموذج التواصل', false)
        ->assertSee('الأسئلة المتكررة', false);
});

it('hides contact sections based on page settings', function () {
    [, $page] = createTenantWithContactPage([
        'show_form' => false,
        'show_social_links' => false,
        'show_contact_info' => false,
        'show_extra_links' => false,
    ]);

    Livewire::test(PageDetail::class, ['slug' => $page->slug])
        ->assertSuccessful()
        ->assertDontSee('نموذج التواصل', false)
        ->assertDontSee('بيانات التواصل', false)
        ->assertDontSee('السوشال ميديا', false);
});

it('builds contact view data from tenant profile and page settings', function () {
    [$tenant, $page] = createTenantWithContactPage([
        'form_fields' => [
            'name' => true,
            'email' => false,
            'phone' => true,
            'message' => true,
            'address' => true,
            'subject' => false,
        ],
        'show_form' => true,
    ]);

    $payload = ContactPageView::make($page, $tenant);

    expect($payload['showForm'])->toBeTrue()
        ->and($payload['formFields']['email'])->toBeFalse()
        ->and($payload['formFields']['address'])->toBeTrue()
        ->and($payload['phone'])->toBe('0501234567')
        ->and($payload['email'])->toBe('hello@example.com')
        ->and($payload['whatsappUrl'])->toBe('https://wa.me/966501234567')
        ->and($payload['showFaqLink'])->toBeTrue()
        ->and($payload['faqUrl'])->not->toBeNull()
        ->and($payload['showReviewsLink'])->toBeFalse();
});

it('shows the reviews link only when the reviews content section is enabled', function () {
    [$tenant, $page] = createTenantWithContactPage();

    $tenant->config = array_merge($tenant->config ?? [], [
        'enabled_content_types' => ['reviews'],
    ]);
    $tenant->save();

    $payload = ContactPageView::make($page->fresh(), $tenant->fresh());

    expect($payload['showReviewsLink'])->toBeTrue();
});

it('hides the faq link when the faq page is inactive', function () {
    [, $page] = createTenantWithContactPage();

    Content::query()
        ->type(contentTypeModel('pages'))
        ->template('faq')
        ->update(['active' => false]);

    $payload = ContactPageView::make($page);

    expect($payload['showFaqLink'])->toBeFalse()
        ->and($payload['faqUrl'])->toBeNull();
});

<?php

use App\Livewire\Tenant\Page\Detail as PageDetail;
use App\Livewire\Tenant\Pages\Contact;
use App\Mail\ContactMessage;
use App\Models\Content;
use App\Models\FormSubmission;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use App\Support\ContactPageView;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

it('submits the contact page form and shows the configured success message', function () {
    Mail::fake();

    [$tenant, $page] = createTenantWithContactPage([
        'success_message' => 'تم استلام رسالتك بنجاح، شكراً لك.',
    ]);

    $tenant->update(['email' => 'tenant-inbox@example.com']);

    Livewire::test('tenant.pages.contact-form', [
        'pageId' => $page->id,
        'formFields' => Content::defaultContactPageData()['form_fields'],
        'successMessage' => 'تم استلام رسالتك بنجاح، شكراً لك.',
    ])
        ->set('name', 'أحمد')
        ->set('email', 'ahmad@example.com')
        ->set('phone', '0501234567')
        ->set('subject', 'استفسار عام')
        ->set('message', 'أريد معرفة المزيد عن خدماتكم.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true)
        ->assertSet('name', 'أحمد')
        ->assertSet('email', 'ahmad@example.com')
        ->assertSet('phone', '0501234567')
        ->assertSet('subject', 'استفسار عام')
        ->assertSet('message', '')
        ->assertSee('تم استلام رسالتك بنجاح، شكراً لك.', false)
        ->assertSee('إرسال الرسالة', false);

    $submission = FormSubmission::query()->first();

    expect($submission)->not->toBeNull()
        ->and($submission->content_id)->toBe($page->id)
        ->and($submission->status)->toBe('new')
        ->and(collect($submission->fields())->pluck('value', 'name')->all())->toMatchArray([
            'name' => 'أحمد',
            'email' => 'ahmad@example.com',
            'phone' => '0501234567',
            'subject' => 'استفسار عام',
            'message' => 'أريد معرفة المزيد عن خدماتكم.',
        ]);

    Mail::assertQueued(ContactMessage::class, function (ContactMessage $mail) use ($tenant): bool {
        return $mail->hasTo('tenant-inbox@example.com')
            && $mail->tenant?->is($tenant)
            && $mail->managePageUrl === route('admin.page.home')
            && $mail->contact['email'] === 'ahmad@example.com'
            && $mail->contact['subject'] === 'استفسار عام'
            && $mail->contact['message'] === 'أريد معرفة المزيد عن خدماتكم.';
    });
});

it('emails the tenant contact profile address when tenant.email is empty', function () {
    Mail::fake();

    [$tenant, $page] = createTenantWithContactPage();

    $tenant->update(['email' => null]);

    Livewire::test('tenant.pages.contact-form', [
        'pageId' => $page->id,
        'formFields' => [
            'name' => true,
            'email' => true,
            'phone' => false,
            'message' => true,
            'address' => false,
            'subject' => false,
        ],
        'successMessage' => Content::defaultContactPageData()['success_message'],
    ])
        ->set('name', 'سارة')
        ->set('email', 'sara@example.com')
        ->set('message', 'مرحباً')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    Mail::assertQueued(ContactMessage::class, function (ContactMessage $mail): bool {
        return $mail->hasTo('hello@example.com')
            && $mail->contact['subject'] === 'رسالة من نموذج اتصل بنا'
            && $mail->contact['name'] === 'سارة';
    });
});

it('validates enabled contact form fields before submit', function () {
    [, $page] = createTenantWithContactPage();

    Livewire::test('tenant.pages.contact-form', [
        'pageId' => $page->id,
        'formFields' => Content::defaultContactPageData()['form_fields'],
        'successMessage' => Content::defaultContactPageData()['success_message'],
    ])
        ->set('name', '')
        ->set('email', 'not-an-email')
        ->set('phone', '')
        ->set('subject', '')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors(['name', 'email', 'phone', 'subject', 'message'])
        ->assertSet('submitted', false);

    expect(FormSubmission::query()->count())->toBe(0);
});

it('uses the default success message when the page message is empty', function () {
    Mail::fake();

    [, $page] = createTenantWithContactPage();

    Livewire::test('tenant.pages.contact-form', [
        'pageId' => $page->id,
        'formFields' => [
            'name' => true,
            'email' => false,
            'phone' => false,
            'message' => true,
            'address' => false,
            'subject' => false,
        ],
        'successMessage' => '',
    ])
        ->set('name', 'سارة')
        ->set('message', 'مرحباً')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true)
        ->assertSet('name', 'سارة')
        ->assertSet('message', '')
        ->assertSee(Content::defaultContactPageData()['success_message'], false)
        ->assertSee('إرسال الرسالة', false);

    Mail::assertQueued(ContactMessage::class);
});

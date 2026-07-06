<?php

use App\Models\Client;
use App\Models\Content;
use App\Models\FormSubmission;
use App\Models\FormSubmissionReply;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);
});

function createTenantWithUserForFormSubmissionDetail(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'test-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    return [$user, $tenant];
}

function createFormSubmissionForTenant(Tenant $tenant, ?Client $client = null): FormSubmission
{
    $form = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'نموذج تواصل',
        'slug' => 'contact-form-'.Str::lower(Str::random(6)),
        'status' => 'published',
        'active' => true,
    ]);

    return FormSubmission::query()->create([
        'tenant_id' => $tenant->id,
        'content_id' => $form->id,
        'client_id' => $client?->id,
        'status' => 'new',
        'data' => [
            'fields' => [
                [
                    'id' => 'name',
                    'name' => 'name',
                    'label' => 'الاسم',
                    'type' => 'text',
                    'value' => 'أحمد محمد',
                ],
                [
                    'id' => 'email',
                    'name' => 'email',
                    'label' => 'البريد الإلكتروني',
                    'type' => 'email',
                    'value' => 'ahmad@example.com',
                ],
                [
                    'id' => 'message',
                    'name' => 'message',
                    'label' => 'الرسالة',
                    'type' => 'textarea',
                    'value' => 'أريد الاستفسار عن الخدمة.',
                ],
            ],
        ],
        'submitted_at' => now(),
    ]);
}

it('renders form submission detail page with structured sections', function () {
    [$user, $tenant] = createTenantWithUserForFormSubmissionDetail();

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'عميل تجريبي',
        'email' => 'client@example.com',
        'phone' => '0512345678',
        'tenant_id' => $tenant->id,
    ]);

    $client->tenants()->attach($tenant->id, [
        'active' => true,
        'meta' => [
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
        ],
    ]);

    $submission = createFormSubmissionForTenant($tenant, $client);

    $this->actingAs($user)
        ->get(route('admin.orders.form-submissions.detail', ['id' => $submission->id]))
        ->assertSuccessful()
        ->assertSee('ملخص الرد')
        ->assertSee('تفاصيل الرد')
        ->assertSee('بيانات الرد')
        ->assertSee('الاسم')
        ->assertSee('أحمد محمد')
        ->assertSee('ahmad@example.com')
        ->assertSee('نموذج تواصل')
        ->assertSee('عرض ملف العميل');
});

it('allows admin to submit a reply on form submission detail page', function () {
    [$user, $tenant] = createTenantWithUserForFormSubmissionDetail();

    $submission = createFormSubmissionForTenant($tenant);

    Livewire::actingAs($user)
        ->test('admin::orders.form-submission-detail', ['id' => $submission->id])
        ->set('replyBody', 'شكراً لتواصلك، سنرد قريباً.')
        ->call('submitReply')
        ->assertHasNoErrors();

    expect(FormSubmissionReply::query()->count())->toBe(1)
        ->and(FormSubmissionReply::query()->first()->body)->toBe('شكراً لتواصلك، سنرد قريباً.');
});

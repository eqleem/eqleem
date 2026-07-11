<?php

use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForForms(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'نماذج تجريبية',
        'handle' => 'forms-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access forms endpoints', function () {
    $this->getJson('/api/forms')->assertUnauthorized();
    $this->postJson('/api/forms', ['title' => 'نموذج'])->assertUnauthorized();
});

test('owner can create list update clone and delete forms', function () {
    [$user, $tenant] = createUserWithTenantForForms();

    $create = $this->actingAs($user)
        ->postJson('/api/forms', ['title' => 'نموذج تواصل'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'نموذج تواصل')
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.published', false);

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->getJson('/api/forms')
        ->assertSuccessful()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonFragment(['uuid' => $uuid]);

    $this->actingAs($user)
        ->getJson("/api/forms/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'نموذج تواصل')
        ->assertJsonStructure([
            'data' => [
                'field_type_options',
                'slug_prefix',
                'fields',
            ],
        ]);

    $this->actingAs($user)
        ->putJson("/api/forms/{$uuid}", [
            'title' => 'نموذج تواصل محدث',
            'description' => 'وصف النموذج',
            'slug' => 'contact-form',
            'published' => true,
            'submit_label' => 'أرسل الآن',
            'success_message' => 'شكراً! تم استلام طلبك.',
            'fields' => [
                [
                    'id' => 'fld_name',
                    'type' => 'text',
                    'label' => 'الاسم الكامل',
                    'name' => 'full_name',
                    'placeholder' => 'اكتب اسمك',
                    'required' => true,
                    'info' => '',
                    'options' => [],
                ],
                [
                    'id' => 'fld_choice',
                    'type' => 'select',
                    'label' => 'الخدمة',
                    'name' => 'service',
                    'placeholder' => '',
                    'required' => false,
                    'info' => '',
                    'options' => ['استشارة', 'تصميم'],
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'نموذج تواصل محدث')
        ->assertJsonPath('data.description', 'وصف النموذج')
        ->assertJsonPath('data.submit_label', 'أرسل الآن')
        ->assertJsonPath('data.success_message', 'شكراً! تم استلام طلبك.')
        ->assertJsonPath('data.published', true)
        ->assertJsonCount(2, 'data.fields');

    setCurrentTenant($tenant);

    $form = Content::query()->where('uuid', $uuid)->first();

    expect($form)->not->toBeNull()
        ->and($form->status)->toBe('published')
        ->and($form->published_at)->not->toBeNull()
        ->and(data_get($form->data, 'description'))->toBe('وصف النموذج')
        ->and(data_get($form->data, 'submit_label'))->toBe('أرسل الآن')
        ->and(data_get($form->data, 'fields.0.name'))->toBe('full_name')
        ->and(data_get($form->data, 'fields.1.options'))->toBe(['استشارة', 'تصميم']);

    $clone = $this->actingAs($user)
        ->postJson("/api/forms/{$uuid}/clone")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'نموذج تواصل محدث ٢')
        ->assertJsonPath('data.status', 'draft');

    $cloneUuid = (string) $clone->json('data.uuid');

    expect($cloneUuid)->not->toBe($uuid);

    $this->actingAs($user)
        ->deleteJson('/api/forms', ['ids' => [$form->id]])
        ->assertSuccessful()
        ->assertJsonPath('data.deleted', 1);
});

test('forms update rejects duplicate field names', function () {
    [$user] = createUserWithTenantForForms();

    $create = $this->actingAs($user)
        ->postJson('/api/forms', ['title' => 'نموذج'])
        ->assertSuccessful();

    $uuid = (string) $create->json('data.uuid');

    $this->actingAs($user)
        ->putJson("/api/forms/{$uuid}", [
            'title' => 'نموذج',
            'slug' => 'form',
            'published' => false,
            'fields' => [
                [
                    'id' => 'fld_1',
                    'type' => 'text',
                    'label' => 'حقل 1',
                    'name' => 'same_name',
                    'required' => false,
                ],
                [
                    'id' => 'fld_2',
                    'type' => 'text',
                    'label' => 'حقل 2',
                    'name' => 'same_name',
                    'required' => false,
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['fields']);
});

test('forms search filters by title', function () {
    [$user, $tenant] = createUserWithTenantForForms();

    setCurrentTenant($tenant);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'Contact Form',
        'slug' => 'contact-form',
        'status' => 'draft',
        'active' => true,
    ]);

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'Feedback Form',
        'slug' => 'feedback-form',
        'status' => 'draft',
        'active' => true,
    ]);

    $this->actingAs($user)
        ->getJson('/api/forms?'.http_build_query(['search' => 'Contact']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Contact Form')
        ->assertJsonPath('meta.total', 1);
});

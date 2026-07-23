<?php

use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
});

it('shows a published active form on the tenant forms detail page', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Form Detail Tenant',
        'handle' => 'form-detail-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $form = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'نموذج طلب خدمة',
        'slug' => 'service-request',
        'data' => [
            'description' => 'املأ الحقول التالية',
            'fields' => [
                [
                    'id' => '1',
                    'name' => 'name',
                    'label' => 'الاسم',
                    'type' => 'text',
                    'required' => true,
                ],
            ],
        ],
        'active' => true,
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('tenant.forms.detail', [
        'tenant' => $tenant->handle,
        'slug' => $form->slug,
    ]))
        ->assertSuccessful()
        ->assertSee('نموذج طلب خدمة', false)
        ->assertSee('املأ الحقول التالية', false)
        ->assertSee('الاسم', false);
});

it('does not show inactive forms on the tenant forms detail page', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Form Detail Inactive',
        'handle' => 'form-off-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $form = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'نموذج معطل',
        'slug' => 'inactive-form',
        'data' => ['fields' => []],
        'active' => false,
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('tenant.forms.detail', [
        'tenant' => $tenant->handle,
        'slug' => $form->slug,
    ]))->assertNotFound();
});

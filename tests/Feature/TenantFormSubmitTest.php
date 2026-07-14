<?php

use App\Models\Content;
use App\Models\FormSubmission;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
});

it('hydrates from prepared fields without querying contents', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Form Submit Tenant',
        'handle' => 'form-submit-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $form = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'Contact',
        'slug' => 'contact-'.Str::lower(Str::random(4)),
        'data' => [
            'description' => 'من قاعدة البيانات',
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

    DB::flushQueryLog();
    DB::enableQueryLog();

    Livewire::test('tenant.forms.submit', [
        'formContentId' => $form->id,
        'blockId' => null,
        'description' => 'وصف جاهز',
        'fields' => [
            [
                'id' => '1',
                'name' => 'name',
                'label' => 'الاسم',
                'type' => 'text',
                'required' => true,
                'placeholder' => '',
                'info' => '',
                'options' => [],
            ],
        ],
    ])
        ->assertSuccessful()
        ->assertSet('description', 'وصف جاهز')
        ->assertSee('الاسم', false);

    $contentQueries = collect(DB::getQueryLog())->filter(
        fn (array $query): bool => str_contains(strtolower($query['query']), 'from "contents"')
            || str_contains(strtolower($query['query']), 'from `contents`')
            || str_contains(strtolower($query['query']), 'from contents')
    );

    expect($contentQueries)->toHaveCount(0);
});

it('submits using prepared fields', function () {
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Form Submit Tenant',
        'handle' => 'form-submit-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $form = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'Contact',
        'slug' => 'contact-'.Str::lower(Str::random(4)),
        'data' => ['fields' => []],
        'active' => true,
        'status' => 'published',
        'published_at' => now(),
    ]);

    Livewire::test('tenant.forms.submit', [
        'formContentId' => $form->id,
        'description' => '',
        'fields' => [
            [
                'id' => '1',
                'name' => 'email',
                'label' => 'البريد',
                'type' => 'email',
                'required' => true,
                'placeholder' => '',
                'info' => '',
                'options' => [],
            ],
        ],
    ])
        ->set('values.email', 'guest@example.com')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    expect(FormSubmission::query()->count())->toBe(1)
        ->and(FormSubmission::query()->first()?->content_id)->toBe($form->id);
});

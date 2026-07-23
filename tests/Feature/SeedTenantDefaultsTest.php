<?php

use App\Actions\CreateDefaultBlocks;
use App\Actions\SeedTenantDefaults;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Support\ContentTypeRegistry;
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

function createTenantForSeedDefaultsTest(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Seed Defaults Tenant',
        'handle' => 'seed-defaults-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    CreateDefaultBlocks::run($tenant);

    return [$user, $tenant];
}

it('seeds a complete contact form with uuid when registering a tenant', function () {
    [, $tenant] = createTenantForSeedDefaultsTest();

    SeedTenantDefaults::run($tenant->fresh());

    $form = Content::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->type(contentTypeModel('forms'))
        ->where('slug', 'contact')
        ->first();

    expect($form)->not->toBeNull()
        ->and($form->uuid)->not->toBeNull()
        ->and($form->title)->toBe('اتصل بنا')
        ->and($form->status)->toBe('published')
        ->and(data_get($form->data, 'fields'))->toHaveCount(4)
        ->and(data_get($form->data, 'description'))->not->toBeEmpty()
        ->and(data_get($form->data, 'submit_label'))->toBe('إرسال')
        ->and(data_get($form->data, 'success_message'))->not->toBeEmpty()
        ->and(data_get($tenant->fresh()->meta, 'bio'))->toBe('صفحة إقليم جديدة');
});

it('seeds default contact, faq, about, terms, and privacy pages when registering a tenant', function () {
    [, $tenant] = createTenantForSeedDefaultsTest();

    SeedTenantDefaults::run($tenant->fresh());

    $contactPage = Content::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->type(contentTypeModel('pages'))
        ->where('template', 'contact')
        ->first();

    $faqPage = Content::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->type(contentTypeModel('pages'))
        ->where('template', 'faq')
        ->first();

    $aboutPage = Content::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->type(contentTypeModel('pages'))
        ->where('template', 'about')
        ->first();

    $termsPage = Content::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->type(contentTypeModel('pages'))
        ->where('slug', 'terms')
        ->first();

    $privacyPage = Content::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->type(contentTypeModel('pages'))
        ->where('slug', 'privacy')
        ->first();

    expect($contactPage)->not->toBeNull()
        ->and($contactPage->uuid)->not->toBeNull()
        ->and($contactPage->title)->toBe('اتصل بنا')
        ->and($contactPage->slug)->toBe('contact-us')
        ->and($contactPage->status)->toBe('published')
        ->and($faqPage)->not->toBeNull()
        ->and($faqPage->uuid)->not->toBeNull()
        ->and($faqPage->title)->toBe('الأسئلة المتكررة')
        ->and($faqPage->slug)->toBe('faq')
        ->and($faqPage->status)->toBe('published')
        ->and($aboutPage)->not->toBeNull()
        ->and($aboutPage->uuid)->not->toBeNull()
        ->and($aboutPage->title)->toBe('من نحن')
        ->and($aboutPage->slug)->toBe('about-us')
        ->and($aboutPage->status)->toBe('published')
        ->and(data_get($aboutPage->data, 'stats'))->toHaveCount(3)
        ->and($termsPage)->not->toBeNull()
        ->and($termsPage->uuid)->not->toBeNull()
        ->and($termsPage->title)->toBe('اتفاقية الاستخدام')
        ->and($termsPage->status)->toBe('published')
        ->and($termsPage->active)->toBeTrue()
        ->and(data_get($termsPage->data, 'body'))->toContain('القبول بالشروط')
        ->and(data_get($termsPage->data, 'body'))->toContain($tenant->name)
        ->and(data_get($termsPage->data, 'editor_mode'))->toBe('html')
        ->and($privacyPage)->not->toBeNull()
        ->and($privacyPage->uuid)->not->toBeNull()
        ->and($privacyPage->title)->toBe('سياسة الخصوصية')
        ->and($privacyPage->status)->toBe('published')
        ->and($privacyPage->active)->toBeTrue()
        ->and(data_get($privacyPage->data, 'body'))->toContain('البيانات التي قد نجمعها')
        ->and(data_get($privacyPage->data, 'body'))->toContain($tenant->name)
        ->and(data_get($privacyPage->data, 'editor_mode'))->toBe('html');
});

it('opens the seeded contact form detail page using its uuid', function () {
    [$user, $tenant] = createTenantForSeedDefaultsTest();

    SeedTenantDefaults::run($tenant->fresh());

    $form = Content::query()
        ->type(contentTypeModel('forms'))
        ->where('slug', 'contact')
        ->firstOrFail();

    $contentType = app(ContentTypeRegistry::class)->find('forms')?->toArray();

    expect($contentType)->not->toBeNull();

    Livewire::actingAs($user)
        ->test('admin::page.content.forms.detail', [
            'contentType' => $contentType,
            'itemId' => $form->uuid,
        ])
        ->assertSee('تحرير النموذج')
        ->assertSee('حقول النموذج')
        ->assertSet('title', 'اتصل بنا');
});

it('backfills missing uuids for existing content records', function () {
    [, $tenant] = createTenantForSeedDefaultsTest();

    $form = Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('forms'),
        'title' => 'Legacy Form',
        'slug' => 'legacy-form',
        'status' => 'draft',
        'active' => true,
    ]);

    Content::query()
        ->withoutGlobalScopes()
        ->whereKey($form->id)
        ->update(['uuid' => null]);

    expect(Content::query()->whereKey($form->id)->value('uuid'))->toBeNull();

    $migration = require database_path('migrations/2026_07_06_041214_backfill_missing_content_uuids.php');
    $migration->up();

    expect(Content::query()->whereKey($form->id)->value('uuid'))->not->toBeNull();
});

it('backfills missing uuids when seed defaults runs for an already seeded tenant', function () {
    [, $tenant] = createTenantForSeedDefaultsTest();

    SeedTenantDefaults::run($tenant->fresh());

    $tenant->meta->set('defaults_seeded', true);
    $tenant->save();

    Content::query()
        ->withoutGlobalScope('tenant')
        ->where('tenant_id', $tenant->id)
        ->update(['uuid' => null]);

    expect(
        Content::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->whereNull('uuid')
            ->count()
    )->toBeGreaterThan(0);

    SeedTenantDefaults::run($tenant->fresh());

    expect(
        Content::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->whereNull('uuid')
            ->count()
    )->toBe(0);
});

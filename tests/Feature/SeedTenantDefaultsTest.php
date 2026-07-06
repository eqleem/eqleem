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
        ->and(data_get($form->data, 'success_message'))->not->toBeEmpty();
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
